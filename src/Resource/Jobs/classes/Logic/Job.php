<?php

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Alg\Obj\MethodFactory as ObjectMethodFactory;
use CeusMedia\Common\UI\OutputBuffer;
use CeusMedia\HydrogenFramework\Logic;
use CeusMedia\Mail\Address\Collection as AddressCollection;
use CeusMedia\Mail\Address\Collection\Parser as AddressCollectionParser;

class Logic_Job extends Logic
{
	protected Model_Job_Schedule $modelSchedule;
	protected Model_Job_Definition $modelDefinition;
	protected Model_Job_Run $modelRun;

	/**
	 *	@param		int|string		$jobRunId
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function archiveJobRun( int|string $jobRunId ): bool
	{
		$statusesNotArchivable	= [
			Model_Job_Run::STATUS_PREPARED,
			Model_Job_Run::STATUS_RUNNING,
		];
		$jobRun	= $this->modelRun->get( $jobRunId );
		if( $jobRun && !$jobRun->archived ){
			if( !in_array( $jobRun->status, $statusesNotArchivable ) ){
				$this->modelRun->edit( $jobRunId, [
					'archived'			=> Model_Job_Run::ARCHIVED_YES,
					'processId'			=> NULL,
					'reportMode'		=> Model_Job_Run::REPORT_MODE_NEVER,
					'reportChannel'		=> Model_Job_Run::REPORT_CHANNEL_NONE,
					'reportReceivers'	=> NULL,
					'message'			=> NULL,
				] );
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 *	Discover jobs of modules which are not registered in database.
	 *	@access		public
	 *	@return		array 		List of discovered job identifiers and their new job definition ID
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function discoverJobDefinitions(): array
	{
		$list			= [];																	//  prepare empty result list
		$discoveredJobs	= [];

		//  read jobs defined by modules
		foreach( $this->env->getModules()->getAll() as $module )									//  iterate all modules
			foreach( $module->jobs as $job )														//  iterate all their jobs
				$discoveredJobs[$job->id]	= $job;													//  collect job by identifier

		//  read jobs defined by XML files, installed by modules
		$model	= new Model_Job( $this->env );
		$model->setFormat( Model_Job::FORMAT_XML );
		$model->load( ['live', 'test', 'dev'] );
		foreach( $model->getAll() as $xmlJobId => $xmlJob ){
			if( !array_key_exists( $xmlJobId, $discoveredJobs ) ){
				$discoveredJobs[$xmlJobId]	= (object) [
					'id'		=> $xmlJobId,
					'class'		=> $xmlJob->class,
					'method'	=> $xmlJob->method,
					'multiple'	=> FALSE,
					'arguments'	=> NULL,
				];
			}
		}
		if( !$discoveredJobs )																		//  no jobs discovered
			return $list;																			//  return empty list

		//  read already registered jobs matching discovered jobs
		$registeredJobIdentifiers	= $this->modelDefinition->getAll(								//  get all registered jobs
			['identifier' => array_keys( $discoveredJobs )],										//  ... having the discovered identifiers
			[],																						//  ... without any orders
			[],																						//  ... without any limits
			['identifier']																			//  ... and return identifiers, only
		);
		if( count( $discoveredJobs ) === count( $registeredJobIdentifiers ) )						//  no new jobs discovered
			return $list;																			//  return empty list

		foreach( $registeredJobIdentifiers as $registeredJobIdentifier )							//  iterate found registered jobs
			unset( $discoveredJobs[$registeredJobIdentifier] );										//  remove discovered job
		foreach( $discoveredJobs as $discoveredJob ){												//  iterate the remaining discovered jobs
			$mode	= Model_Job_Definition::MODE_SINGLE;											//  assume mode is "single"
			if( $discoveredJob->multiple )															//  job is marked as "multiple"
				$mode	= Model_Job_Definition::MODE_MULTIPLE;										//  set mode to "multiple"
			$arguments	= [];																		//  assume no arguments
			if( $discoveredJob->arguments )															//  job as defined arguments
				$arguments	= $discoveredJob->arguments;											//  carry arguments
			$jobDefinitionId	= $this->modelDefinition->add( [									//  register job in database
				'mode'			=> $mode,
				'status'		=> Model_Job_Definition::STATUS_ENABLED,
				'identifier'	=> $discoveredJob->id,
				'className'		=> $discoveredJob->class,
				'methodName'	=> $discoveredJob->method,
				'arguments'		=> json_encode( $arguments ),
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			] );
			$list[$discoveredJob->id]	= $jobDefinitionId;											//  note identifier and new job definition ID
		}
		return $list;																				//  return result list
	}

	/**
	 *	@param		int|string		$jobDefinitionId
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getDefinition( int|string $jobDefinitionId ): ?object
	{
		return $this->modelDefinition->get( $jobDefinitionId );
	}

	public function getDefinitionByIdentifier( string $jobDefinitionIdentifier, array $extendBy = [] ): ?object
	{
		$jobDefinition	= $this->modelDefinition->getByIndex( 'identifier', $jobDefinitionIdentifier );
		if( $jobDefinition ){
			if( $extendBy ){
				if( in_array( 'schedules', $extendBy ) ){
					$jobDefinition->schedules	= $this->modelSchedule->getAll( [
						'jobDefinitionId'	=> $jobDefinition->jobDefinitionId,
					] );
				}
				if( in_array( 'runs', $extendBy ) ){
					$jobDefinition->runs		= $this->modelRun->getAll( [
						'jobDefinitionId'	=> $jobDefinition->jobDefinitionId,
					] );
				}
			}
			return $jobDefinition;
		}
		return NULL;
	}

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@param		array		$fields
	 *	@return		array
	 */
	public function getDefinitions( array $conditions = [], array $orders = [], array $limits = [], array $fields = [] ): array
	{
		return $this->modelDefinition->getAll( $conditions, $orders, $limits, $fields );
	}

	/**
	 *	Returns list of job runs not having a process anymore.
	 *	Gets running jobs and checks if process ID is still a running process.
	 *	@access		public
	 *	@param		array		$conditions		Additional job run filters (status will be ignored)
	 *	@param		array		$orders			Order rules (defaults to: ranAt -> DESC)
	 *	@return		array						List ob job run objects not having a process anymore
	 */
	public function getDiscontinuedJobRuns( array $conditions = [], array $orders = [] ): array
	{
		$list			= [];
		$orders			= $orders ?: ['ranAt' => 'ASC'];
		foreach( $this->getRunningJobs( $conditions, $orders ) as $runningJob )
			if( !$this->isActiveProcessId( (int) $runningJob->processId ) )
				$list[$runningJob->jobRunId]	= $runningJob;
		return $list;
	}

	public function getRunningJobs( array $conditions = [], array $orders = [], array $limits = [], array $fields = [] ): array
	{
		$conditions['status']	= Model_Job_Run::STATUS_RUNNING;
		return $this->modelRun->getAll( $conditions, $orders, $limits, $fields );
	}

	/**
	 *	Return list of prepared job runs.
	 *	@access		public
	 *	@param		int|string		$jobRunId		ID of job run
	 *	@param		array			$extendBy		List of data extensions (definition, schedules)
	 *	@return		object|NULL	Found prepared job run
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getPreparedJobRun( int|string $jobRunId, array $extendBy = [] ): ?object
	{
		$jobRun	= $this->modelRun->get( $jobRunId );
		if( $jobRun && $extendBy ){
			if( in_array( 'schedules', $extendBy ) && $jobRun->jobScheduleId )
				$jobRun->schedule	= $this->modelSchedule->get( $jobRun->jobScheduleId );
			if( in_array( 'definition', $extendBy ) )
				$jobRun->definition		= $this->modelDefinition->get( $jobRun->jobDefinitionId );
		}
		return $jobRun;
	}

	/**
	 *	Return list of prepared job runs,
	 *	@access		public
	 *	@param		int|string		$jobDefinitionId	ID of job definition to filter by (optional)
	 *	@param		array			$extendBy			List of data extensions (definition, schedules)
	 *	@return		array			list of found prepared job runs
	 *	@todo		remove
	 *	@deprecated	seems to be unused
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getPreparedJobRuns( int|string $jobDefinitionId, array $extendBy = [] ): array
	{
		$indices	= ['status' => Model_Job_Run::STATUS_PREPARED];
		if( $jobDefinitionId )
			$indices['jobDefinitionId']	= $jobDefinitionId;
		$preparedJobs	= $this->modelRun->getAllByIndices( $indices, ['createdAt' => 'ASC'] );
		foreach( $preparedJobs as $preparedJob ){
			if( in_array( 'definition', $extendBy ) )
				$preparedJob->definition	= $this->modelDefinition->get( $preparedJob->jobDefinitionId );
			if( in_array( 'schedules', $extendBy ) )
				$preparedJob->schedules	= $this->modelSchedule->getAll( [
					'jobDefinitionId'	=> $preparedJob->jobDefinitionId,
				] );
		}
		return $preparedJobs;
	}

	/**
	 *	@param		array		$conditions
	 *	@return		array
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getScheduledJobs( array $conditions = [] ): array
	{
		$conditions	= array_merge( [
			'status'	=> 1,
		], $conditions );
		$list	= $this->modelSchedule->getAll( $conditions );
		foreach( $list as $item ){
			$item->definition	= $this->modelDefinition->get( $item->jobDefinitionId );
			$item->latestRuns	= $this->modelRun->getAll(
				['jobScheduleId'	=> $item->jobScheduleId],
				['modifiedAt'		=> 'DESC'],
				[0, 10]
			);
		}
		return $list;
	}

	/**
	 *	@return		bool
	 */
	public function hasRunningExclusiveJob(): bool
	{
		$exclusiveJobsDefinitionIds	= $this->modelDefinition->getAllByIndices( [
			'mode'		=> Model_Job_Definition::MODE_EXCLUSIVE,
			'status'	=> Model_Job_Definition::STATUS_ENABLED,
		], [], [], ['jobDefinitionId'] );
		if( $exclusiveJobsDefinitionIds ){
			$exclusiveJobIsRunning	= $this->modelRun->getByIndices( [
				'jobDefinitionId'	=> $exclusiveJobsDefinitionIds,
				'status'			=> Model_Job_Run::STATUS_RUNNING,
			] );
			if( $exclusiveJobIsRunning )
				return TRUE;
		}
		return FALSE;
	}

	public function isActiveProcessId( int $processId ): bool
	{
		if( $processId < 2 )
			return FALSE;
		exec( 'ps -p '.$processId, $table );
		return count( $table ) > 1;
	}

	public function isRunningSingleJob( object $jobDefinition, ?int $runType = NULL ): bool
	{
		if( (int) $jobDefinition->mode !== Model_Job_Definition::MODE_SINGLE )
			return FALSE;
		$conditions	= ['jobDefinitionId' => $jobDefinition->jobDefinitionId];
		if( !is_null( $runType ) )
			$conditions['type']	= $runType;
		return (bool) count( $this->getRunningJobs( $conditions ) );
	}

	/**
	 *	@param		string		$error
	 *	@return		self
	 *	@throws		ReflectionException
	 */
	public function logError( string $error ): self
	{
//		$message	= $t->getMessage().'@'.$t->getFile().':'.$t->getLine().PHP_EOL.$t->getTraceAsString();
		$this->env->getLog()->log( "error", $error );
//		$payload	= ['exception' => $t];
//		$this->env->getCaptain()->callHook( 'Env', 'logException', $this, $payload );
		return $this;
	}

	/**
	 *	@param		Throwable		$t
	 *	@return		self
	 *	@throws		ReflectionException
	 */
	public function logException( Throwable $t ): self
	{
		$message	= $t->getMessage().'@'.$t->getFile().':'.$t->getLine().PHP_EOL.$t->getTraceAsString();
		$this->env->getLog()->log( "error", $message );
		$payload	= ['exception' => $t];
		$this->env->getCaptain()->callHook( 'Env', 'logException', $this, $payload );
		return $this;
	}

	/**
	 *	@param		object		$job
	 *	@param		array		$options
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function prepareManuallyJobRun( object $job, array $options ): ?object
	{
		if( !$this->isPreparableJob( $job, Model_Job_Run::TYPE_MANUALLY ) )
			return NULL;
		$entityData	= array_merge( [
			'jobDefinitionId'	=> $job->jobDefinitionId,
			'processId'			=> getmypid(),
			'type'				=> Model_Job_Run::TYPE_MANUALLY,
			'status'			=> Model_Job_Run::STATUS_PREPARED,
			'title'				=> '',
//			'message'			=> json_encode( [] ),
			'createdAt'			=> time(),
			'modifiedAt'		=> time(),
		], $options );
		$jobRunId	= $this->modelRun->add( $entityData );
		return $this->getPreparedJobRun( $jobRunId );
	}

	/**
	 *	@param		int|string|NULL		$jobDefinitionId
	 *	@return		array
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function prepareScheduledJobs( int|string|NULL $jobDefinitionId = NULL ): array
	{
		$scheduledJobsToPrepare	= $this->getScheduledJobsToPrepare( $jobDefinitionId );
		return $this->prepareJobRunsForScheduledJobs( $scheduledJobsToPrepare );
	}

	/**
	 *	Runs prepared job.
	 *	@access		public
	 *	@todo		deprecated? see ::startJobRun()
	 *	@todo		implement! serial or (better) in parallel?
	 *	@todo		exception handling?
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function runPreparedJob( int|string $jobRunId ): void
	{
		$jobRun	= $this->getPreparedJobRun( $jobRunId, ['definition'] );
		if( Model_Job_Run::STATUS_PREPARED !== (int) $jobRun->status )
			throw new RuntimeException( 'Job run is not in prepared state' );
		if( $jobRun->definition && !$jobRun->processId ){
			try{
				$this->modelRun->edit( $jobRun->jobRunId, [
					'status'		=> Model_Job_Run::STATUS_RUNNING,
					'processId'		=> getmypid(),
					'modifiedAt'	=> time(),
				] );
//				exec( $jobRun->method );
			}
			catch( Exception ){

			}
		}
	}

	/**
	 *	@param		int|string		$jobRunId
	 *	@param		int				$status
	 *	@param		array			$messageData
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function quitJobRun( int|string $jobRunId, int $status, array $messageData = [] ): bool
	{
		$jobRun	= $this->modelRun->get( $jobRunId );
		if( Model_Job_Run::STATUS_RUNNING !== (int) $jobRun->status ){
			if( $status === Model_Job_Run::STATUS_TERMINATED )
				return FALSE;
			throw new RuntimeException( 'Job (id: '.$jobRun->jobDefinitionId.') is not running (status: '.$jobRun->status.')' );
		}
		if( !in_array( $status, Model_Job_Run::STATUS_TRANSITIONS[$jobRun->status], TRUE ) )
			throw new DomainException( 'Transition to given status is not allowed' );
		$dataRun	= [
			'status'		=> $status,
			'modifiedAt'	=> time(),
			'finishedAt'	=> time(),
			'message'		=> json_encode( $messageData ),
		];
		if( Model_Job_Run::STATUS_TERMINATED === $status )
			if( $this->isActiveProcessId( (int) $jobRun->processId ) )
				$this->killJobRunProcess( (int) $jobRun->processId );
		$this->modelRun->edit( $jobRun->jobRunId, $dataRun );
		$jobDefinition	= $this->modelDefinition->get( $jobRun->jobDefinitionId );
		$dataDefinition	= [];
		if( in_array( $status, Model_Job_Run::STATUSES_NEGATIVE, TRUE ) )
			$dataDefinition['fails']	= $jobDefinition->fails + 1;
		if( $dataDefinition )
			$this->modelDefinition->edit( $jobRun->jobDefinitionId, $dataDefinition );
		return TRUE;
	}

	/**
	 *	@param		object		$jobRun
	 *	@param		array		$commands
	 *	@param		array		$parameters
	 *	@return		int
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function startJobRun( object $jobRun, array $commands = [], array $parameters = [] ): int
	{
		if( Model_Job_Run::STATUS_PREPARED !== (int) $jobRun->status )
			throw new RuntimeException( 'Job run is not in prepared state' );
		$this->modelRun->edit( $jobRun->jobRunId, [
			'status'		=> Model_Job_Run::STATUS_RUNNING,
			'processId'		=> getmypid(),
			'modifiedAt'	=> time(),
			'ranAt'			=> time(),
		] );

		$jobDefinition	= $this->modelDefinition->get( $jobRun->jobDefinitionId );
		$this->modelDefinition->edit( $jobRun->jobDefinitionId, [
			'runs'		=> $jobDefinition->runs + 1,
			'lastRunAt'	=> time(),
		] );

		if( $jobRun->jobScheduleId ){
			$jobSchedule	= $this->modelSchedule->get( $jobRun->jobScheduleId );
			$this->modelSchedule->edit( $jobRun->jobScheduleId, [
				'lastRunAt'	=> time(),
			] );
		}


		$className	= 'Job_'.$jobDefinition->className;												//  build job class name
		$classArgs	= [$this->env, $this];															//  prepare job class instance arguments
		$arguments	= [$commands, $parameters];														//
		$methodName	= $jobDefinition->methodName;													//  shortcut method name
		$jobObject	= ObjectFactory::createObject( '\\'.$className, $classArgs );				//  ... create job class instance with arguments
		$jobObject->noteJob( $jobDefinition->className, $methodName );								//  ... inform job instance about method to be called
		$jobObject->noteArguments( $commands, $parameters );										//  ... inform job instance about request arguments

		$returnCode		= -255;
		$result			= -255;
		$output			= '';
		try{																						//  try to ...

			$outputBuffer	= new OutputBuffer( FALSE );
			if( $jobRun->type == Model_Job_Run::TYPE_SCHEDULED )
				$outputBuffer->open();
			$factory	= new ObjectMethodFactory( $jobObject );								//  create a factory for this job
			$result		= $factory->callMethod( $methodName, $arguments );							//  call job method with arguments

			if( $jobRun->type == Model_Job_Run::TYPE_SCHEDULED )
				$output	= $outputBuffer->get( TRUE );
			$this->quitJobRun( (int) $jobRun->jobRunId, Model_Job_Run::STATUS_DONE, [			//  finish job run since no exception has been thrown
				'type'		=> 'data',																//  ... and save message of type "data"
				'code'		=> $result,																//  ... containing the method call return code
				'data'		=> $jobObject->getResults(),											//  ... and results collected by the method call
				'output'	=> $output,
			] );
			if( is_integer( $result ) )																//  method call return value is an integer
				$returnCode	 = $result;																//  carry this return code out as status
/*			if( strlen( trim( $result ) ) )															//  handle old return strings @deprecated
				foreach( explode( "\n", trim( $result ) ) as $line )								//  handle each result line
					$this->log( $line );															//  by logging
*/			$returnCode = 1;																		//  quit with positive status
		}
		catch( Throwable $t ){																		//  on throwable error or exception
			$this->quitJobRun( (int) $jobRun->jobRunId, Model_Job_Run::STATUS_FAILED, [		//  finish job run as failed
				'type'		=> 'throwable',															//  ... and save message of type "throwable" for caught exception
				'exception'	=> get_class( $t ),
				'message'	=> $t->getMessage(),													//  ...
				'code'		=> $t->getCode(),														//  ...
				'file'		=> $t->getFile(),														//  ...
				'line'		=> $t->getLine(),														//  ...
				'trace'		=> $t->getTraceAsString(),												//  ...
			] );
			if( (int) $jobRun->type === Model_Job_Run::TYPE_MANUALLY ){								//  since job has been run manually
				throw new RuntimeException( 'Job run failed: '.$t->getMessage(), 0, $t );			//  ... carry exception out
			}
		}
		finally{
			$this->sendReport( (int) $jobRun->jobRunId, $commands, $parameters, $result );
		}
		return $returnCode;																					//  quit with negative status
	}

	/**
	 *	@param		int|string		$jobRunId
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeJobRun( int|string $jobRunId ): bool
	{
		$job			= $this->modelRun->get( $jobRunId );
		if( $job && in_array( (int) $job->status, Model_Job_Run::STATUSES_ARCHIVABLE, TRUE ) ){
			$this->modelRun->remove( $jobRunId );
			return TRUE;
		}
		return FALSE;
	}

	/**
	 *	@param		string|NULL		$reason
	 *	@return		array
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function terminateDiscontinuedJobRuns( ?string $reason = NULL ): array
	{
		$list	= [];
		foreach( $this->getDiscontinuedJobRuns() as $jobRun ){
			$messageData	= $reason ? ['reason' => $reason] : [];
			$this->quitJobRun( (int) $jobRun->jobRunId, Model_Job_Run::STATUS_TERMINATED, $messageData );
			$list[(int) $jobRun->jobRunId]	= (object) [
				'jobRunId'			=> (int) $jobRun->jobRunId,
				'jobDefinitionId'	=> (int) $jobRun->jobDefinitionId,
				'jobScheduleId'		=> (int) $jobRun->jobScheduleId,
				'jobDefinition'		=> $this->getDefinition( (int) $jobRun->jobDefinitionId ),
			];
		}
		return $list;
	}

/*	public function setProcessIdOnJobRun( $jobRunOrJobRunId ): ?object
	{
		$jobRunId	= $jobRunOrJobRunId;
		if( is_object( $jobRunOrJobRunId ) )
			$jobRunId	= $jobRunOrJobRunId->jobRunId;
		$this->modelRun->edit( $jobRunId, [
			'processId'		=> getmypid(),
			'modifiedAt'	=> time(),
		] );
		return $this->modelRun->get( $jobRunId );
	}*/

	/*  --  PROTECTED  --  */

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function __onInit(): void
	{
		$this->modelDefinition	= new Model_Job_Definition( $this->env );
		$this->modelSchedule	= new Model_Job_Schedule( $this->env );
		$this->modelRun			= new Model_Job_Run( $this->env );
		$this->discoverJobDefinitions();
	}

	/**
	 *	@param		int|string		$jobDefinitionId
	 *	@return		array
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function abortPreparedJobRuns( int|string $jobDefinitionId ): array
	{
		$preparedJobs	= $this->modelRun->getAllByIndices( [
			'jobDefinitionId'	=> $jobDefinitionId,
			'status'		=> Model_Job_Run::STATUS_PREPARED
		] );
		foreach( $preparedJobs as $preparedJob ){
			$this->modelRun->edit( $preparedJob->jobRunId, [
				'status'		=> Model_Job_Run::STATUS_ABORTED,
				'modifiedAt'	=> time(),
			] );
		}
		return $preparedJobs;
	}

	/**
	 *	@param		int|string|NULL		$jobDefinitionId
	 *	@return		array
	 *	@throws		ReflectionException
	 */
	protected function getScheduledJobsToPrepare( int|string|NULL $jobDefinitionId = NULL ): array
	{
		$jobSchedules	= [];
		$indices		= [
			'status'	=> Model_Job_Schedule::STATUS_ENABLED,
			'type'		=> [
				Model_Job_Schedule::TYPE_CRON,
				Model_Job_Schedule::TYPE_INTERVAL,
				Model_Job_Schedule::TYPE_DATETIME,
			],
		];
		if( $jobDefinitionId )
			$indices['jobDefinitionId']	= $jobDefinitionId;
		foreach( $this->modelSchedule->getAllByIndices( $indices ) as $jobSchedule ){
			try{
				$isDue	= FALSE;
				switch( (int) $jobSchedule->type ){
					case Model_Job_Schedule::TYPE_CRON:
						$cron	= new Cron\CronExpression( $jobSchedule->expression );
						$isDue = $cron->isDue();
					break;
					case Model_Job_Schedule::TYPE_INTERVAL:
						$isDue		= empty( $jobSchedule->lastRunAt );								//  not running before -> always due
						if( !$isDue ){																//  otherwise do some math
							$interval	= new DateInterval( $jobSchedule->expression );				//  create date interval
							$lastRun	= new DateTimeImmutable( '@'.$jobSchedule->lastRunAt );		//  get datetime object for last run
							$nextRun	= $lastRun->add( $interval );
							$isDue		= new DateTime( 'now' ) >= $nextRun;						//  last run + interval is past -> due
						}
						break;
					case Model_Job_Schedule::TYPE_DATETIME:
						$isDue		= $jobSchedule->expression === date( 'Y-m-d H:i' );
						break;
				}
				if( $isDue )
					$jobSchedules[]	= $jobSchedule;
			}
			catch( Exception $e ){
				$payload	= ['exception' => $e];
				$this->callHook( 'Env', 'logException', $this, $payload );
			}
		}
		return $jobSchedules;
	}

	protected function isPreparableJob( object $jobDefinition, ?int $runType = 0 ): bool
	{
		$preparableJobStatuses	= [Model_Job_Definition::STATUS_ENABLED];
		if( !in_array( (int) $jobDefinition->status, $preparableJobStatuses, TRUE ) )
			return FALSE;

//		$this->terminateDiscontinuedJobRuns( 'Cleanup on next job run' );
		if( $this->hasRunningExclusiveJob() )
			return FALSE;

		switch( (int) $jobDefinition->mode ){
			case Model_Job_Definition::MODE_MULTIPLE:
				return TRUE;
			case Model_Job_Definition::MODE_EXCLUSIVE:
				$jobsAreRunning	= (bool) count( $this->getRunningJobs() );
				if( $jobsAreRunning )		// @todo finish impl: exclude currently "running" job "run scheduled jobs" to make this work
					return FALSE;
				break;
			case Model_Job_Definition::MODE_SINGLE:
				if( $this->isRunningSingleJob( $jobDefinition, $runType ) )
					return FALSE;
				break;
		}
		return TRUE;
	}

	/**
	 *	Terminates job run process by ID.
	 *	@access		protected
	 *	@param		integer		$processId		ID of Process to kill
	 *	@return		boolean
	 */
	protected function killJobRunProcess( int $processId ): bool
	{
		$command	= 'kill '.$processId;
		exec( $command );
		return !$this->isActiveProcessId( $processId );
	}

	/**
	 *	@param 		array $scheduledJobRunsToPrepare
	 *	@return		array
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function prepareJobRunsForScheduledJobs( array $scheduledJobRunsToPrepare ): array
	{
		$list	= [];
		foreach( $scheduledJobRunsToPrepare as $scheduledJob ){
			$date	= date( 'Y-m-d-H-i' );
			$job	= $this->modelDefinition->get( $scheduledJob->jobDefinitionId );
			if( $this->isPreparableJob( $job ) ){
				$this->abortPreparedJobRuns( $scheduledJob->jobDefinitionId );
				$jobRunId	= $this->modelRun->add( [
					'jobScheduleId'		=> $scheduledJob->jobScheduleId,
					'jobDefinitionId'	=> $scheduledJob->jobDefinitionId,
					'type'				=> Model_Job_Run::TYPE_SCHEDULED,
					'status'			=> Model_Job_Run::STATUS_PREPARED,
					'title'				=> $scheduledJob->title,
					'arguments'			=> $scheduledJob->arguments,
					'reportMode'		=> $scheduledJob->reportMode,
					'reportChannel'		=> $scheduledJob->reportChannel,
					'reportReceivers'	=> $scheduledJob->reportReceivers,
//					'message'			=> json_encode( [] ),
					'createdAt'			=> time(),
					'modifiedAt'		=> time(),
				] );
				$list[$jobRunId]	= $this->modelRun->get( $jobRunId );
			}
		}
		return $list;
	}

	/**
	 *	@param		int|string		$jobRunId
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function isToReport( int|string $jobRunId/*, ?int $mode = NULL*/ ): bool
	{
		$jobRun			= $this->modelRun->get( $jobRunId );
		$status			= (int) $jobRun->status;
		$reportMode		= (int) $jobRun->reportMode;

/*		if( is_int( $mode ) ){
			if( !in_array( $mode, Model_Job_Run::REPORT_MODES ) )
				throw new RangeException( 'Invalid job run report mode given' );
			$reportMode		= $mode;
		}*/

		switch( $reportMode ){
			case Model_Job_Run::REPORT_MODE_NEVER:
				return FALSE;
			case Model_Job_Run::REPORT_MODE_ALWAYS:
				return TRUE;
			case Model_Job_Run::REPORT_MODE_FAIL:
				return $status === Model_Job_Run::STATUS_FAILED;
			case Model_Job_Run::REPORT_MODE_DONE:
				return $status === Model_Job_Run::STATUS_DONE;
			case Model_Job_Run::REPORT_MODE_SUCCESS:
				return $status === Model_Job_Run::STATUS_SUCCESS;
/*			case Model_Job_Run::REPORT_MODE_NEGATIVE:
				return in_array( $status, [
					Model_Job_Run::STATUS_TERMINATED,
					Model_Job_Run::STATUS_FAILED,
					Model_Job_Run::STATUS_ABORTED,
				] );*/
/*			case Model_Job_Run::REPORT_MODE_ERROR:			//  @todo to implement
				return in_array( $status, [
				] );*/
/*			case Model_Job_Run::REPORT_MODE_POSITIVE:
				return in_array( $status, [
					Model_Job_Run::STATUS_DONE,
					Model_Job_Run::STATUS_SUCCESS,
				] );*/
			case Model_Job_Run::REPORT_MODE_CHANGE:
				$previousRunStatus	= $this->modelRun->getByIndices( [
						'jobRunId'			=> '< '.$jobRunId,
						'jobDefinitionId'	=> $jobRun->jobDefinitionId,
					], ['jobRunId' => 'DESC'], ['status'] );
				if( $previousRunStatus ){
					$statusMap		= [
						'positive'	=> [
							Model_Job_Run::STATUS_DONE,
							Model_Job_Run::STATUS_SUCCESS,
						],
						'negative'	=> [
							Model_Job_Run::STATUS_FAILED,
						]
					];
					$nowIsNeg	= in_array( $status, $statusMap['negative'] );
					$nowIsPos	= in_array( $status, $statusMap['positive'] );
					$prevIsNeg	= in_array( $previousRunStatus, $statusMap['negative'] );
					$prevIsPos	= in_array( $previousRunStatus, $statusMap['positive'] );
					if( ( $nowIsPos && $prevIsNeg ) || ( $nowIsNeg && $prevIsPos ) )
						return TRUE;
				}
				return FALSE;
		}
		return FALSE;
	}

	/**
	 *	@param		int|string $jobRunId
	 *	@param		array $commands
	 *	@param		array $parameters
	 *	@param		$resultCode
	 *	@return		int|NULL
	 *	@throws ReflectionException
	 *	@throws \Psr\SimpleCache\InvalidArgumentException
	 */
	protected function sendReport( int|string $jobRunId, array $commands, array $parameters, $resultCode ): ?int
	{
		$jobRun		= $this->modelRun->get( $jobRunId );
		$message	= json_decode( $jobRun->message ?: '{"type": "unknown"}' );

		if( !$this->isToReport( $jobRunId ) )
			return 0;

		$receivers	= $jobRun->reportReceivers;
		$parser		= new AddressCollectionParser();
		$receivers	= $parser->parse( $receivers );
		if( !count( $receivers ) )
			return 0;

		$jobDefinition	= $this->modelDefinition->get( $jobRun->jobDefinitionId );
		$results		= (array) json_decode( $jobRun->message ?: '[]' );
		$mailData		= [
			'arguments'		=> (object) [
				'commands'		=> $commands,
				'parameters'	=> $parameters
			],
			'definition'	=> $jobDefinition,
			'run'			=> $jobRun,
			'result'		=> (object) array_merge( [
				'status'	=> $jobRun->status,
				'code'		=> $resultCode,													//  ... containing the method call return code
				'type'		=> 'empty',														//  ... and save message of type "data"
				'data'		=> NULL,														//  ... and results collected by the method call
			], $results ),
		];

		switch( (int) $jobRun->reportChannel ){
			case Model_Job_Run::REPORT_CHANNEL_MAIL:
				$this->sendReportViaMail( $jobRunId, $mailData, $receivers );
				break;
			case Model_Job_Run::REPORT_CHANNEL_XMPP:
				$this->sendReportViaXMPP( $jobRunId, $mailData, $receivers );
				break;
		}
		return count( $receivers );
	}

	/**
	 *	@param		int|string			$jobRunId
	 *	@param		array				$mailData
	 *	@param		AddressCollection	$receivers
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function sendReportViaMail( int|string $jobRunId, array $mailData, AddressCollection $receivers ): void
	{
		$logicMail	= $this->env->getLogic()->get( 'Mail' );
		$language	= $this->env->getLanguage()->getLanguage();
		$mail		= new Mail_Job_Report( $this->env, $mailData );
		foreach( $receivers as $address ){
			$receiver	= (object) ['email' => $address->getAddress()];
			$logicMail->handleMail( $mail, $receiver, $language );
		}
	}

	protected function sendReportViaXMPP( int|string $jobRunId, $mailData, $receivers )
	{
		throw new RuntimeException( 'No implemented, yet' );
	}
}
