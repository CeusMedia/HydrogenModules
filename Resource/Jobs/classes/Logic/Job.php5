<?php
class Logic_Job extends CMF_Hydrogen_Logic
{
	protected $modelSchedule;
	protected $modelDefinition;
	protected $modelRun;

	protected function __onInit()
	{
		$this->modelDefinition	= new Model_Job_Definition( $this->env );
		$this->modelSchedule	= new Model_Job_Schedule( $this->env );
		$this->modelRun			= new Model_Job_Run( $this->env );
		$this->discoverJobDefinitions();
	}

	public function archiveJobRun( int $jobRunId ): bool
	{
		$statusesNotArchivable	= array(
			Model_Job_Run::STATUS_PREPARED,
			Model_Job_Run::STATUS_RUNNING,
		);
		$jobRun	= $this->modelRun->get( $jobRunId );
		if( $jobRun && !$jobRun->archived ){
			if( !in_array( $jobRun->status, $statusesNotArchivable ) ){
				$this->modelRun->edit( $jobRunId, array(
					'archived'			=> Model_Job_Run::ARCHIVED_YES,
					'processId'			=> NULL,
					'reportMode'		=> Model_Job_Run::REPORT_MODE_NEVER,
					'reportChannel'		=> Model_Job_Run::REPORT_CHANNEL_NONE,
					'reportReceivers'	=> NULL,
					'message'			=> NULL,
				) );
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 *	Discover jobs of modules which are not registered in database.
	 *	@access		public
	 *	@return		array 		List of discovered job identifiers and their new job definition ID
	 */
	public function discoverJobDefinitions(): array
	{
		$list			= array();																	//  prepare empty result list
		$discoveredJobs	= array();

		//  read jobs definied by modules
		foreach( $this->env->getModules()->getAll() as $module )									//  iterate all modules
			foreach( $module->jobs as $job )														//  iterate all their jobs
				$discoveredJobs[$job->id]	= $job;													//  collect job by identifier

		//  read jobs definied by XML files, installed by modules
		$model	= new Model_Job( $this->env );
		$model->setFormat( Model_Job::FORMAT_XML );
		$model->load( array( 'live', 'test', 'dev' ) );
		foreach( $model->getAll() as $xmlJobId => $xmlJob ){
			if( !array_key_exists( $xmlJobId, $discoveredJobs ) ){
				$discoveredJobs[$xmlJobId]	= (object) array(
					'id'		=> $xmlJobId,
					'class'		=> $xmlJob->class,
					'method'	=> $xmlJob->method,
					'multiple'	=> FALSE,
					'arguments'	=> NULL,
				);
			}
		}
		if( !$discoveredJobs )																		//  no jobs discovered
			return $list;																			//  return empty list

		//  read already registered jobs matching discovered jobs
		$registeredJobIdentifiers	= $this->modelDefinition->getAll(								//  get all registered jobs
			array( 'identifier' => array_keys( $discoveredJobs ) ),									//  ... having the discovered identifiers
			array(),																				//  ... without any orders
			array(),																				//  ... without any limits
			array( 'identifier' )																	//  ... and return identifiers, only
		);
		if( count( $discoveredJobs ) === count( $registeredJobIdentifiers ) )						//  no new jobs discovered
			return $list;																			//  return empty list

		foreach( $registeredJobIdentifiers as $registeredJobIdentifier )							//  iterate found registered jobs
			unset( $discoveredJobs[$registeredJobIdentifier] );										//  remove discovered job
		foreach( $discoveredJobs as $discoveredJob ){												//  iterate the remaining discovered jobs
			$mode	= Model_Job_Definition::MODE_SINGLE;											//  assume mode is "single"
			if( $discoveredJob->multiple )															//  job is marked as "multiple"
				$mode	= Model_Job_Definition::MODE_MULTIPLE;										//  set mode to "multiple"
			$arguments	= array();																	//  assume no arguments
			if( $discoveredJob->arguments )															//  job as defined arguments
				$arguments	= $discoveredJob->arguments;											//  carry arguments
			$jobDefinitionId	= $this->modelDefinition->add( array(								//  register job in database
				'mode'			=> $mode,
				'status'		=> Model_Job_Definition::STATUS_ENABLED,
				'identifier'	=> $discoveredJob->id,
				'className'		=> $discoveredJob->class,
				'methodName'	=> $discoveredJob->method,
				'arguments'		=> json_encode( $arguments ),
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			) );
			$list[$discoveredJob->id]	= $jobDefinitionId;											//  note identifier and new job definition ID
		}
		return $list;																				//  return result list
	}

	public function getDefinition( int $jobDefinitionId )
	{
		return $this->modelDefinition->get( $jobDefinitionId );
	}

	public function getDefinitionByIdentifier( string $jobDefinitionIdentifier, array $extendBy = array() ): ?object
	{
		$jobDefinition	= $this->modelDefinition->getByIndex( 'identifier', $jobDefinitionIdentifier );
		if( $jobDefinition ){
			if( $extendBy ){
				if( in_array( 'schedules', $extendBy ) ){
					$jobDefinition->schedules	= $this->modelSchedule->getAll( array(
						'jobDefinitionId'	=> $jobDefinition->jobDefinitionId,
					) );
				}
				if( in_array( 'runs', $extendBy ) ){
					$jobDefinition->runs		= $this->modelRun->getAll( array(
						'jobDefinitionId'	=> $jobDefinition->jobDefinitionId,
					) );
				}
			}
			return $jobDefinition;
		}
		return NULL;
	}

	public function getDefinitions( $conditions = array(), $orders = array(), $limits = array(), $fields = array() ): array
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
	public function getDiscontinuedJobRuns( $conditions = array(), $orders = array() ): array
	{
		$list			= array();
		$orders			= $orders ? $orders : array( 'ranAt' => 'ASC' );
		foreach( $this->getRunningJobs( $conditions, $orders ) as $runningJob )
			if( !$this->isActiveProcessId( (int) $runningJob->processId ) )
				$list[$runningJob->jobRunId]	= $runningJob;
		return $list;
	}

	public function getRunningJobs( $conditions = array(), $orders = array(), $limits = array(), $fields = array() ): array
	{
		$conditions['status']	= Model_Job_Run::STATUS_RUNNING;
		return $this->modelRun->getAll( $conditions, $orders, $limits, $fields );
	}

	/**
	 *	Return list of preared job runs.
	 *	@access		public
	 *	@param		int			$jobRunId		ID of job run
	 *	@param		array		$extendBy		List of data extensions (definition, schedules)
	 *	@return		object|NULL	Found prepared job run
	 */
	public function getPreparedJobRun( $jobRunId, $extendBy = array() ): object
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
	 *	Return list of preared job runs,
	 *	@access		public
	 *	@param		int			$jobDefinitionId	ID of job defintion to filter by (optional)
	 *	@param		array		$extendBy			List of data extensions (definition, schedules)
	 *	@return		array		list of found prepared job runs
	 */
	public function getPreparedJobRuns( $jobDefinitionId = NULL, $extendBy = array() ): array
	{
		$indices	= array( 'status' => Model_Job_Run::STATUS_PREPARED );
		if( $jobDefinitionId )
			$indices['jobDefinitionId']	= $jobDefinitionId;
		$preparedJobs	= $this->modelRun->getAllByIndices( $indices, array( 'createdAt' => 'ASC' ) );
		foreach( $preparedJobs as $preparedJob ){
			if( in_array( 'definition', $extendBy ) )
				$preparedJob->definition	= $this->modelDefinition->get( $preparedJob->jobDefinitionId );
			if( in_array( 'schedules', $extendBy ) )
				$preparedJob->schedules	= $this->modelSchedule->getAll( array(
					'jobDefinitionId'	=> $preparedJob->jobDefinitionId,
				) );
		}
		return $preparedJobs;
	}

	public function getScheduledJobs( $conditions = array() ): array
	{
		$conditions	= array_merge( array(
			'status'	=> 1,
		), $conditions );
		$list	= $this->modelSchedule->getAll( $conditions );
		foreach( $list as $nr => $item ){
			$list[$nr]->definition		= $this->modelDefinition->get( $item->jobDefinitionId );
			$list[$nr]->latestRuns	= $this->modelRun->getAll(
				array( 'jobScheduleId' => $item->jobScheduleId ),
				array( 'modifiedAt' => 'DESC' ),
				array( 0, 10 )
			);
		}
		return $list;
	}

	public function hasRunningExclusiveJob(): bool
	{
		$exclusiveJobsDefinitionIds	= $this->modelDefinition->getAllByIndices( array(
			'mode'		=> Model_Job_Definition::MODE_EXCLUSIVE,
			'status'	=> Model_Job_Definition::STATUS_ENABLED,
		), array(), array(), array( 'jobDefinitionId' ) );
		if( $exclusiveJobsDefinitionIds ){
			$exclusiveJobIsRunning	= $this->modelRun->getByIndices( array(
				'jobDefinitionId'	=> $exclusiveJobsDefinitionIds,
				'status'		=> Model_Job_Run::STATUS_RUNNING,
			) );
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

	public function isRunningSingleJob( $jobDefinition, ?int $runType = NULL ): bool
	{
		if( (int) $jobDefinition->mode !== Model_Job_Definition::MODE_SINGLE )
			return FALSE;
		$conditions	= array( 'jobDefinitionId' => $jobDefinition->jobDefinitionId );
		if( !is_null( $runType ) )
			$conditions['type']	= $runType;
		return (bool) count( $this->getRunningJobs( $conditions ) );
	}

	public function logException( Throwable $t ): self
	{
		$message	= $t->getMessage().'@'.$t->getFile().':'.$t->getLine().PHP_EOL.$t->getTraceAsString();
		$this->env->getLog()->log( "error", $message );
		$this->env->getCaptain()->callHook( 'Env', 'logException', $this, array( 'exception' => $t ) );
		return $this;
	}

	public function prepareManuallyJobRun( object $job, array $options ): ?object
	{
		if( !$this->isPreparableJob( $job, Model_Job_Run::TYPE_MANUALLY ) )
			return NULL;
		$entityData	= array_merge( array(
			'jobDefinitionId'	=> $job->jobDefinitionId,
			'processId'			=> getmypid(),
			'type'				=> Model_Job_Run::TYPE_MANUALLY,
			'status'			=> Model_Job_Run::STATUS_PREPARED,
			'title'				=> '',
//			'message'			=> json_encode( array() ),
			'createdAt'			=> time(),
			'modifiedAt'		=> time(),
		), $options );
		$jobRunId	= $this->modelRun->add( $entityData );
		return $this->getPreparedJobRun( $jobRunId );
	}

	public function prepareScheduledJobs( ?int $jobDefinitionId = NULL ): array
	{
		$scheduledJobsToPrepare	= $this->getScheduledJobsToPrepare( $jobDefinitionId );
		$preparedJobs	= $this->prepareJobRunsForScheduledJobs( $scheduledJobsToPrepare );
		return $preparedJobs;
	}

	/**
	 *	Runs prepared job.
	 *	@access		public
	 *	@todo		deprecated? see ::startJobRun()
	 *	@todo		implement! serial or (better) in parallel?
	 *	@todo		exception handling?
	 */
	public function runPreparedJob( int $jobRunId )
	{
		$jobRun	= $this->getPreparedJobRun( $jobRunId, array( 'definition' ) );
		if( (int) $jobRun->status !== Model_Job_Run::STATUS_PREPARED )
			throw new \RuntimeException( 'Job run is not in prepared state' );
		if( $jobRun->definition && !$jobRun->processId ){
			try{
				$this->modelRun->edit( $jobRun->jobRunId, array(
					'status'		=> Model_Job_Run::STATUS_RUNNING,
					'processId'		=> getmypid(),
					'modifiedAt'	=> time(),
				) );
//				exec( $jobRun->method );
			}
			catch( Exception $e ){

			}
		}
	}

	public function quitJobRun( int $jobRunId, int $status, $messageData = array() )
	{
		$jobRun	= $this->modelRun->get( $jobRunId );
		if( (int) $jobRun->status !== Model_Job_Run::STATUS_RUNNING )
			throw new \RuntimeException( 'Job is not running' );
		if( !in_array( $status, Model_Job_Run::STATUS_TRANSITIONS[$jobRun->status] ) )
			throw new \DomainException( 'Transition to given status is not allowed' );
		$dataRun	= array(
			'status'		=> $status,
			'modifiedAt'	=> time(),
			'finishedAt'	=> time(),
			'message'		=> json_encode( $messageData ),
		);
		if( $status === Model_Job_Run::STATUS_TERMINATED )
			if( $this->isActiveProcessId( (int) $jobRun->processId ) )
				$this->killJobRunProcess( (int) $jobRun->processId );
		$this->modelRun->edit( $jobRun->jobRunId, $dataRun );
		$jobDefinition	= $this->modelDefinition->get( $jobRun->jobDefinitionId );
		$dataDefinition	= array();
		if( in_array( $status, Model_Job_Run::STATUSES_NEGATIVE ) )
			$dataDefinition['fails']	= $jobDefinition->fails + 1;
		if( $dataDefinition )
			$this->modelDefinition->edit( $jobRun->jobDefinitionId, $dataDefinition );
	}

	public function startJobRun( object $jobRun, $commands = array(), $parameters = array() ): int
	{
		if( (int) $jobRun->status !== Model_Job_Run::STATUS_PREPARED )
			throw new RuntimeException( 'Job run is not in prepared state' );
		$this->modelRun->edit( $jobRun->jobRunId, array(
			'status'		=> Model_Job_Run::STATUS_RUNNING,
			'processId'		=> getmypid(),
			'modifiedAt'	=> time(),
			'ranAt'			=> time(),
		) );

		$jobDefinition	= $this->modelDefinition->get( $jobRun->jobDefinitionId );
		$this->modelDefinition->edit( $jobRun->jobDefinitionId, array(
			'runs'		=> $jobDefinition->runs + 1,
			'lastRunAt'	=> time(),
		) );

		if( $jobRun->jobScheduleId ){
			$jobSchedule	= $this->modelSchedule->get( $jobRun->jobScheduleId );
			$this->modelSchedule->edit( $jobRun->jobScheduleId, array(
				'lastRunAt'	=> time(),
			) );
		}

		$className	= 'Job_'.$jobDefinition->className;												//  build job class name
		$classArgs	= array( $this->env, $this );													//  prepare job class instance arguments
		$arguments	= array( $commands, $parameters );												//
		$methodName	= $jobDefinition->methodName;													//  shortcut method name
		$jobObject	= \Alg_Object_Factory::createObject( '\\'.$className, $classArgs );				//  ... create job class instance with arguments
		$jobObject->noteJob( $jobDefinition->className, $methodName );								//  ... inform job instance about method to be called
		$jobObject->noteArguments( $commands, $parameters );										//  ... inform job instance about request arguments

		$returnCode		= -255;
		$result			= -255;
		$output			= '';
		try{																						//  try to ...
			$outputBuffer	= new UI_OutputBuffer( FALSE );
			if( $jobRun->type == Model_Job_Run::TYPE_SCHEDULED )
				$outputBuffer->open();
			$result		= \Alg_Object_MethodFactory::call( $jobObject, $methodName, $arguments );	//  ... call job method of job instance with arguments
			if( $jobRun->type == Model_Job_Run::TYPE_SCHEDULED )
				$output	= $outputBuffer->get( TRUE );
			$this->quitJobRun( (int) $jobRun->jobRunId, Model_Job_Run::STATUS_DONE, array(			//  finish job run since no exception has been thrown
				'type'		=> 'data',																//  ... and save message of type "data"
				'code'		=> $result,																//  ... containing the method call return code
				'data'		=> $jobObject->getResults(),											//  ... and results collected by the method call
				'output'	=> $output,
			) );
			if( is_integer( $result ) )																//  method call return value is an integer
				$returnCode	 = $result;																//  carry this return code out as status
/*			if( strlen( trim( $result ) ) )															//  handle old return strings @deprecated
				foreach( explode( "\n", trim( $result ) ) as $line )								//  handle each result line
					$this->log( $line );															//  by logging
*/			$returnCode = 1;																		//  quit with positive status
		}
		catch( Throwable $t ){																		//  on throwable error or exception
			$this->quitJobRun( (int) $jobRun->jobRunId, Model_Job_Run::STATUS_FAILED, array(		//  finish job run as failed
				'type'		=> 'throwable',															//  ... and save message of type "throwable" for caught exception
				'message'	=> $t->getMessage(),													//  ...
				'code'		=> $t->getCode(),														//  ...
				'file'		=> $t->getFile(),														//  ...
				'line'		=> $t->getLine(),														//  ...
				'trace'		=> $t->getTraceAsString(),												//  ...
			) );
			if( (int) $jobRun->type === Model_Job_Run::TYPE_MANUALLY ){								//  since job has been run manually
				throw new RuntimeException( 'Job run failed: '.$t->getMessage(), 0, $t );			//  ... carry exception out
			}
		}
		finally{
			$this->sendReport( (int) $jobRun->jobRunId, $commands, $parameters, $result );
		}
		return $returnCode;																					//  quit with negative status
	}

	public function terminateDiscontinuedJobRuns( ?string $reason = NULL ): array
	{
		$list	= array();
		foreach( $this->getDiscontinuedJobRuns() as $jobRun ){
			$messageData	= $reason ? array( 'reason' => $reason ) : array();
			$this->quitJobRun( (int) $jobRun->jobRunId, Model_Job_Run::STATUS_TERMINATED, $messageData );
			$list[(int) $jobRun->jobRunId]	= (object) array(
				'jobRunId'			=> (int) $jobRun->jobRunId,
				'jobDefinitionId'	=> (int) $jobRun->jobDefinitionId,
				'jobScheduleId'		=> (int) $jobRun->jobScheduleId,
				'jobDefinition'		=> $this->getDefinition( (int) $jobRun->jobDefinitionId ),
			);
		}
		return $list;
	}

/*	public function setProcessIdOnJobRun( $jobRunOrJobRunId ): ?object
	{
		$jobRunId	= $jobRunOrJobRunId;
		if( is_object( $jobRunOrJobRunId ) )
			$jobRunId	= $jobRunOrJobRunId->jobRunId;
		$this->modelRun->edit( $jobRunId, array(
			'processId'		=> getmypid(),
			'modifiedAt'	=> time(),
		) );
		return $this->modelRun->get( $jobRunId );
	}*/

	/*  --  PROTECTED  --  */

	protected function abortPreparedJobRuns( $jobDefinitionId ): array
	{
		$preparedJobs	= $this->modelRun->getAllByIndices( array(
			'jobDefinitionId'	=> $jobDefinitionId,
			'status'		=> Model_Job_Run::STATUS_PREPARED
		) );
		foreach( $preparedJobs as $preparedJob ){
			$this->modelRun->edit( $preparedJob->jobRunId, array(
				'status'		=> Model_Job_Run::STATUS_ABORTED,
				'modifiedAt'	=> time(),
			) );
		}
		return $preparedJobs;
	}

	protected function getScheduledJobsToPrepare( $jobDefinitionId = NULL ): array
	{
		$jobSchedules	= array();
		$indices		= array(
			'status'	=> Model_Job_Schedule::STATUS_ENABLED,
			'type'		=> array(
				Model_Job_Schedule::TYPE_CRON,
				Model_Job_Schedule::TYPE_INTERVAL,
				Model_Job_Schedule::TYPE_DATETIME,
			),
		);
		if( $jobDefinitionId )
			$indices['jobDefinitionId']	= $jobDefinitionId;
		foreach( $this->modelSchedule->getAllByIndices( $indices ) as $jobSchedule ){
			try{
				$isDue	= FALSE;
				switch( (int) $jobSchedule->type ){
					case Model_Job_Schedule::TYPE_CRON:
						$cron	= Cron\CronExpression::factory( $jobSchedule->expression );
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
				$this->callHook( 'Env', 'logException', $this, array( 'exception' => $e ) );
			}
		}
		return $jobSchedules;
	}

	protected function isPreparableJob( object $jobDefinition, ?int $runType = 0 ): bool
	{
		$preparableJobStatuses	= array( Model_Job_Definition::STATUS_ENABLED );
		if( !in_array( (int) $jobDefinition->status, $preparableJobStatuses, TRUE ) )
			return FALSE;
		$this->terminateDiscontinuedJobRuns( 'Cleanup on next job run' );
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

	protected function prepareJobRunsForScheduledJobs( $scheduledJobRunsToPrepare ): array
	{
		$list	= array();
		foreach( $scheduledJobRunsToPrepare as $scheduledJob ){
			$date	= date( 'Y-m-d-H-i' );
			$job	= $this->modelDefinition->get( $scheduledJob->jobDefinitionId );
			if( $this->isPreparableJob( $job ) ){
				$this->abortPreparedJobRuns( $scheduledJob->jobDefinitionId );
				$jobRunId	= $this->modelRun->add( array(
					'jobScheduleId'		=> $scheduledJob->jobScheduleId,
					'jobDefinitionId'	=> $scheduledJob->jobDefinitionId,
					'type'				=> Model_Job_Run::TYPE_SCHEDULED,
					'status'			=> Model_Job_Run::STATUS_PREPARED,
					'title'				=> $scheduledJob->title,
					'arguments'			=> $scheduledJob->arguments,
					'reportMode'		=> $scheduledJob->reportMode,
					'reportChannel'		=> $scheduledJob->reportChannel,
					'reportReceivers'	=> $scheduledJob->reportReceivers,
//					'message'			=> json_encode( array() ),
					'createdAt'			=> time(),
					'modifiedAt'		=> time(),
				) );
				$list[$jobRunId]	= $this->modelRun->get( $jobRunId );
			}
		}
		return $list;
	}

	protected function isToReport( $jobRunId/*, ?int $mode = NULL*/ ): bool
	{
		$jobRun			= $this->modelRun->get( $jobRunId );
		$status			= (int) $jobRun->status;
		$reportMode		= (int) $jobRun->reportMode;

/*		if( is_int( $mode ) ){
			if( !in_array( $mode, Model_Job_Run::REPORT_MODES ) )
				throw new \RangeException( 'Invalid job run report mode given' );
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
				return in_array( $status, array(
					Model_Job_Run::STATUS_TERMINATED,
					Model_Job_Run::STATUS_FAILED,
					Model_Job_Run::STATUS_ABORTED,
				) );*/
/*			case Model_Job_Run::REPORT_MODE_ERROR:			//  @todo to implement
				return in_array( $status, array(
				) );*/
/*			case Model_Job_Run::REPORT_MODE_POSITIVE:
				return in_array( $status, array(
					Model_Job_Run::STATUS_DONE,
					Model_Job_Run::STATUS_SUCCESS,
				) );*/
			case Model_Job_Run::REPORT_MODE_CHANGE:
				$previousRunStatus	= $this->modelRun->getByIndices(
					array(
						'jobRunId'			=> '< '.$jobRunId,
						'jobDefinitionId'	=> $jobRun->jobDefinitionId,
					),
					array( 'jobRunId' => 'DESC' ),
					array( 'status' )
				);
				if( $previousRunStatus ){
					$statusMap		= array(
						'positive'	=> array(
							Model_Job_Run::STATUS_DONE,
							Model_Job_Run::STATUS_SUCCESS,
						),
						'negative'	=> array(
							Model_Job_Run::STATUS_FAILED,
						)
					);
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

	protected function sendReport( $jobRunId, $commands, $parameters, $resultCode )
	{
		$jobRun		= $this->modelRun->get( $jobRunId );
		$message	= json_decode( $jobRun->message ?: '{"type": "unknown"}' );

		if( !$this->isToReport( $jobRunId ) )
			return 0;

		$receivers	= $jobRun->reportReceivers;
		$parser		= new \CeusMedia\Mail\Address\Collection\Parser();
		$receivers	= $parser->parse( $receivers );
		if( !count( $receivers ) )
			return 0;


		$jobDefinition	= $this->modelDefinition->get( $jobRun->jobDefinitionId );
		$results		= (array) json_decode( $jobRun->message ?: '[]' );
		$mailData		= array(
			'arguments'		=> (object) array(
				'commands'		=> $commands,
				'parameters'	=> $parameters
			),
			'definition'	=> $jobDefinition,
			'run'			=> $jobRun,
			'result'		=> (object) array_merge( array(
				'status'	=> $jobRun->status,
				'code'		=> $resultCode,													//  ... containing the method call return code
				'type'		=> 'empty',														//  ... and save message of type "data"
				'data'		=> NULL,														//  ... and results collected by the method call
			), $results ),
		);

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

	protected function sendReportViaMail( $jobRunId, $mailData, $receivers )
	{
		$logicMail	= $this->env->getLogic()->get( 'Mail' );
		$language	= $this->env->getLanguage()->getLanguage();
		$mail		= new Mail_Job_Report( $this->env, $mailData );
		foreach( $receivers as $address ){
			$receiver	= (object) array( 'email' => $address->getAddress() );
			$logicMail->handleMail( $mail, $receiver, $language );
		}
	}

	protected function sendReportViaXMPP( $jobRunId, $mailData, $receivers )
	{
		throw new \RuntimeException( 'No implemented, yet' );
	}
}
