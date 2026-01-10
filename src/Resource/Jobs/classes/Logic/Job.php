<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Alg\Obj\MethodFactory as ObjectMethodFactory;
use CeusMedia\Common\UI\OutputBuffer;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;
use CeusMedia\HydrogenFramework\Logic;
use CeusMedia\Mail\Address\Collection as AddressCollection;
use CeusMedia\Mail\Address\Collection\Parser as AddressCollectionParser;

class Logic_Job extends Logic
{
	protected Model_Job_Schedule $modelSchedule;
	protected Model_Job_Definition $modelDefinition;
	protected Model_Job_Run $modelRun;
	protected ModuleDefinition $module;

	/**
	 *	@param		int|string		$jobRunId
	 *	@return		bool
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
					'processId'			=> 0,
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
	 *	@return		array<string,Entity_Job_Definition> 	List of discovered jobs
	 *	@throws		ReflectionException
	 */
	public function discoverJobDefinitions(): array
	{
		$list			= [];																		//  prepare empty result list
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
			$entity	= Entity_Job_Definition::fromArray( [
				'mode'			=> $mode,
				'status'		=> Model_Job_Definition::STATUS_ENABLED,
				'identifier'	=> $discoveredJob->id,
				'className'		=> $discoveredJob->class,
				'methodName'	=> $discoveredJob->method,
				'arguments'		=> json_encode( $arguments ),
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			] );
			$jobDefinitionId	= $this->modelDefinition->add( $entity );							//  register job in database
			$entity->jobDefinitionId	= $jobDefinitionId;
			$list[$discoveredJob->id]	= $entity;													//  note identifier and new job definition
		}
		return $list;																				//  return result list
	}

	/**
	 *	@param		int|string		$jobDefinitionId
	 *	@return		?Entity_Job_Definition
	 */
	public function getDefinition( int|string $jobDefinitionId ): ?Entity_Job_Definition
	{
		/** @var ?Entity_Job_Definition $definition */
		$definition	= $this->modelDefinition->get( $jobDefinitionId );
		return $definition;
	}

	public function getDefinitionByIdentifier( string $jobDefinitionIdentifier, array $extendBy = [] ): ?Entity_Job_Definition
	{
		/** @var ?Entity_Job_Definition $jobDefinition */
		$jobDefinition	= $this->modelDefinition->getByIndex( 'identifier', $jobDefinitionIdentifier );
		if( NULL === $jobDefinition )
			return NULL;
		if( $extendBy ){
			if( in_array( 'schedules', $extendBy ) ){
				$jobDefinition->schedules	= $this->modelSchedule->getAll( [
					'jobDefinitionId'	=> $jobDefinition->jobDefinitionId,
				] );
			}
		}
		return $jobDefinition;
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

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@param		array		$fields
	 *	@return		array
	 */
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
	 *	@return		?Entity_Job_Run	Found prepared job run
	 */
	public function getPreparedJobRun( int|string $jobRunId, array $extendBy = [] ): ?Entity_Job_Run
	{
		/** @var ?Entity_Job_Run $jobRun */
		$jobRun	= $this->modelRun->get( $jobRunId );
		if( NULL !== $jobRun && $extendBy ){
			if( in_array( 'schedules', $extendBy ) && $jobRun->jobScheduleId ){
				/** @var Entity_Job_Schedule $schedule */
				$schedule	= $this->modelSchedule->get( $jobRun->jobScheduleId );
				$jobRun->schedule	= $schedule;
			}
			if( in_array( 'definition', $extendBy ) ){
				/** @var Entity_Job_Definition $definition */
				$definition	= $this->modelDefinition->get( $jobRun->jobDefinitionId );
				$jobRun->definition		= $definition;
			}
		}
		return $jobRun;
	}

	/**
	 *	Return list of prepared job runs,
	 *	@access		public
	 *	@param		int|string		$jobDefinitionId	ID of job definition to filter by (optional)
	 *	@param		array			$extendBy			List of data extensions (definition, schedules)
	 *	@return		Entity_Job_Run[]					list of found prepared job runs
	 *	@todo		remove
	 *	@deprecated	seems to be unused
	 */
	public function getPreparedJobRuns( int|string $jobDefinitionId, array $extendBy = [] ): array
	{
		$indices	= ['status' => Model_Job_Run::STATUS_PREPARED];
		if( $jobDefinitionId )
			$indices['jobDefinitionId']	= $jobDefinitionId;
		/** @var Entity_Job_Run[] $preparedJobs */
		$preparedJobs	= $this->modelRun->getAllByIndices( $indices, ['createdAt' => 'ASC'] );
		foreach( $preparedJobs as $preparedJob ){
			if( in_array( 'definition', $extendBy ) ){
				/** @var Entity_Job_Definition $definition */
				$definition	= $this->modelDefinition->get( $preparedJob->jobDefinitionId );
				$preparedJob->definition	= $definition;
			}
			if( in_array( 'schedules', $extendBy ) )
				$preparedJob->schedules		= $this->modelSchedule->getAll( [
					'jobDefinitionId'	=> $preparedJob->jobDefinitionId,
				] );
		}
		return $preparedJobs;
	}

	/**
	 *	@param		array		$conditions
	 *	@return		array
	 */
	public function getScheduledJobs( array $conditions = [] ): array
	{
		$conditions	= array_merge( [
			'status'	=> Model_Job_Schedule::STATUS_ENABLED,
		], $conditions );
		/** @var Entity_Job_Schedule[] $list */
		$list	= $this->modelSchedule->getAll( $conditions );
		foreach( $list as $item ){
			/** @var Entity_Job_Definition $definition */
			$definition			= $this->modelDefinition->get( $item->jobDefinitionId );
			$item->definition	= $definition;
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
		if( [] === $exclusiveJobsDefinitionIds )
			return FALSE;
		$exclusiveJobIsRunning	= $this->modelRun->countByIndices( [
			'jobDefinitionId'	=> $exclusiveJobsDefinitionIds,
			'status'			=> Model_Job_Run::STATUS_RUNNING,
		] );
		return 0 !== $exclusiveJobIsRunning;
	}

	/**
	 *	@param		int		$processId
	 *	@return		bool
	 */
	public function isActiveProcessId( int $processId ): bool
	{
		if( $processId < 2 )
			return FALSE;
		exec( 'ps -p '.$processId, $table );
		return count( $table ) > 1;
	}

	/**
	 *	@param		Entity_Job_Definition	$jobDefinition
	 *	@param		?int					$runType
	 *	@return		bool
	 */
	public function isRunningSingleJob( Entity_Job_Definition $jobDefinition, ?int $runType = NULL ): bool
	{
		if( Model_Job_Definition::MODE_SINGLE !== $jobDefinition->mode )
			return FALSE;
		$conditions	= ['jobDefinitionId' => $jobDefinition->jobDefinitionId];
		if( NULL !== $runType )
			$conditions['type']	= $runType;
		return [] !== $this->getRunningJobs( $conditions );
	}

	/**
	 *	@param		string		$error
	 *	@return		self
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
	 *	@param		Entity_Job_Definition	$job
	 *	@param		array					$options
	 *	@return		?Entity_Job_Run
	 */
	public function prepareManuallyJobRun( Entity_Job_Definition $job, array $options ): ?Entity_Job_Run
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
	 */
	public function runPreparedJob( int|string $jobRunId ): void
	{
		$jobRun	= $this->getPreparedJobRun( $jobRunId, ['definition'] );
		if( NULL === $jobRun )
			throw new RuntimeException( 'Job is not prepared' );
		if( Model_Job_Run::STATUS_PREPARED !== $jobRun->status )
			throw new RuntimeException( 'Job run is not in prepared state' );
		if( NULL !== $jobRun->definition && 0 === $jobRun->processId ){
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
	 *	@param		Entity_Job_Result|array			$messageData
	 *	@return		bool
	 */
	public function quitJobRun( int|string $jobRunId, int $status, Entity_Job_Result|array $messageData = [] ): bool
	{
		/** @var ?Entity_Job_Run $jobRun */
		$jobRun	= $this->modelRun->get( $jobRunId );
		if( Model_Job_Run::STATUS_RUNNING !== $jobRun->status ){
			if( Model_Job_Run::STATUS_TERMINATED === $status )
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
			if( $this->isActiveProcessId( $jobRun->processId ) )
				$this->killJobRunProcess( $jobRun->processId );
		$this->modelRun->edit( $jobRun->jobRunId, $dataRun );
		/** @var ?Entity_Job_Definition $jobDefinition */
		$jobDefinition	= $this->modelDefinition->get( $jobRun->jobDefinitionId );
		$dataDefinition	= [];
		if( in_array( $status, Model_Job_Run::STATUSES_NEGATIVE, TRUE ) )
			$dataDefinition['fails']	= $jobDefinition->fails + 1;
		if( $dataDefinition )
			$this->modelDefinition->edit( $jobRun->jobDefinitionId, $dataDefinition );
		return TRUE;
	}

	/**
	 *	@param		Entity_Job_Run	$jobRun
	 *	@param		array			$commands
	 *	@param		array			$parameters
	 *	@return		int
	 *	@throws		ReflectionException
	 */
	public function startJobRun( Entity_Job_Run $jobRun, array $commands = [], array $parameters = [] ): int
	{
		if( Model_Job_Run::STATUS_PREPARED !== $jobRun->status )
			throw new RuntimeException( 'Job run is not in prepared state' );
		$this->modelRun->edit( $jobRun->jobRunId, [
			'status'		=> Model_Job_Run::STATUS_RUNNING,
			'processId'		=> getmypid(),
			'modifiedAt'	=> time(),
			'ranAt'			=> time(),
		] );

		/** @var ?Entity_Job_Definition $jobDefinition */
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
		$classArgs	= [$this->env];																	//  prepare job class instance arguments
		$arguments	= [$commands, $parameters];														//
		$methodName	= $jobDefinition->methodName;													//  shortcut method name
		/** @var Job_Abstract $jobObject */
		$jobObject	= ObjectFactory::createObject( '\\'.$className, $classArgs );			//  ... create job class instance with arguments
		$jobObject->noteJob( $jobDefinition->className, $methodName );								//  ... inform job instance about method to be called
		$jobObject->noteArguments( $commands, $parameters );										//  ... inform job instance about request arguments

		$returnCode		= -255;
		$result			= -255;
		$output			= '';
		try{																						//  try to ...

			$outputBuffer	= new OutputBuffer( FALSE );
			if( Model_Job_Run::TYPE_SCHEDULED === $jobRun->type )
				$outputBuffer->open();
			$factory	= new ObjectMethodFactory( $jobObject );									//  create a factory for this job
			$result		= $factory->callMethod( $methodName, $arguments );							//  call job method with arguments

			if( Model_Job_Run::TYPE_SCHEDULED === $jobRun->type )
				$output	= $outputBuffer->get( TRUE );

			$finalStatus	= Model_Job_Run::STATUS_DONE;
			$runResults		= $jobObject->getResults();
			if( $runResults instanceof Entity_Job_Result )
				if( Entity_Job_Result::STATUS_SUCCESS === $runResults->status )
					$finalStatus	= Model_Job_Run::STATUS_SUCCESS;

			$this->quitJobRun( (int) $jobRun->jobRunId, $finalStatus, [								//  finish job run since no exception has been thrown
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
			if( Model_Job_Run::TYPE_MANUALLY === $jobRun->type ){									//  since job has been run manually
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
	 */
	protected function __onInit(): void
	{
		$this->modelDefinition	= new Model_Job_Definition( $this->env );
		$this->modelSchedule	= new Model_Job_Schedule( $this->env );
		$this->modelRun			= new Model_Job_Run( $this->env );
		$this->module			= $this->env->getModules()->get( 'Resource_Jobs' );
		if( 'always' === $this->module->config['discover']->value )
			$this->discoverJobDefinitions();
	}

	/**
	 *	@param		int|string		$jobDefinitionId
	 *	@return		array
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

	protected function isPreparableJob( Entity_Job_Definition $jobDefinition, ?int $runType = 0 ): bool
	{
		$preparableJobStatuses	= [Model_Job_Definition::STATUS_ENABLED];
		if( !in_array( $jobDefinition->status, $preparableJobStatuses, TRUE ) )
			return FALSE;

//		$this->terminateDiscontinuedJobRuns( 'Cleanup on next job run' );
		if( $this->hasRunningExclusiveJob() )
			return FALSE;

		switch( $jobDefinition->mode ){
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
	 *	@param 		array		$scheduledJobRunsToPrepare
	 *	@return		array<int,Entity_Job_Run>
	 */
	protected function prepareJobRunsForScheduledJobs( array $scheduledJobRunsToPrepare ): array
	{
		/** @var array<int,Entity_Job_Run> $list */
		$list	= [];
		foreach( $scheduledJobRunsToPrepare as $scheduledJob ){
			$date	= date( 'Y-m-d-H-i' );
			/** @var ?Entity_Job_Definition $jobDefinition */
			$jobDefinition	= $this->modelDefinition->get( $scheduledJob->jobDefinitionId );
			if( NULL !== $jobDefinition && $this->isPreparableJob( $jobDefinition ) ){
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
				/** @var Entity_Job_Run $run */
				$run	= $this->modelRun->get( $jobRunId );
				$list[$jobRunId]	= $run;
			}
		}
		return $list;
	}

	/**
	 *	@param		int|string		$jobRunId
	 *	@return		bool
	 */
	protected function isToReport( int|string $jobRunId/*, ?int $mode = NULL*/ ): bool
	{
		/** @var Entity_Job_Run $jobRun */
		$jobRun			= $this->modelRun->get( $jobRunId );
		$status			= $jobRun->status;
		$reportMode		= $jobRun->reportMode;

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
	 *	@param		int|string		$jobRunId
	 *	@param		array			$commands
	 *	@param		array			$parameters
	 *	@param		$resultCode
	 *	@return		int|NULL
	 *	@throws		ReflectionException
	 */
	protected function sendReport( int|string $jobRunId, array $commands, array $parameters, $resultCode ): ?int
	{
		/** @var Entity_Job_Run $jobRun */
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
