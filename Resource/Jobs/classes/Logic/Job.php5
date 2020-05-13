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

	/**
	 *	Discover jobs of modules which are not registered in database.
	 *	@access		public
	 *	@return		array 		List of discovered job identifiers and their new job definition ID
	 */
	public function discoverJobDefinitions(): array
	{
		$discoveredJobs	= array();
		foreach( $this->env->getModules()->getAll() as $module )									//  iterate all modules
			foreach( $module->jobs as $job )														//  iterate all their jobs
				$discoveredJobs[$job->id]	= $job;													//  collect job by identifier
		$registeredJobIdentifiers	= $this->modelDefinition->getAll(								//  get all registered jobs
			array( 'identifier' => array_keys( $discoveredJobs ) ),									//  ... having the discovered identifiers
			array(),																				//  ... without any orders
			array(),																				//  ... without any limits
			array( 'identifier' )																	//  ... and return identifiers, only
		);
		$list	= array();																			//  prepare empty result list
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

	/**
	 *	Runs prepared job.
	 *	@access		public
	 *	@todo		deprecated? see ::startJobRun()
	 *	@todo		implement! serial or (better) in parallel?
	 *	@todo		exception handling?
	 */
	public function runPreparedJob( $jobRunId )
	{
		$jobRun	= $this->getPreparedJobRun( $jobRunId, array( 'definition' ) );
		if( (int) $jobRun->status !== Model_Job_Run::STATUS_PREPARED )
			throw new RuntimeException( 'Job run is not in prepared state' );
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

	public function getRunningJobs( $conditions = array(), $orders = array(), $limits = array(), $fields = array() ): array
	{
		$conditions['status']	= Model_Job_Run::STATUS_RUNNING;
		return $this->modelRun->getAll( $conditions, $orders, $limits, $fields );
	}

	public function getDefinition( $jobDefinitionId )
	{
		return $this->modelDefinition->get( $jobDefinitionId );
	}

	public function getDefinitionByIdentifier( $jobDefinitionIdentifier, $extendBy = array() ): ?object
	{
		$jobDefinition	= $this->modelDefinition->getByIndex( 'identifier', $jobDefinitionIdentifier );
		if( $jobDefinition && $extendBy ){
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

	public function getDefinitions( $conditions = array(), $orders = array(), $limits = array(), $fields = array() ): array
	{
		return $this->modelDefinition->getAll( $conditions, $orders, $limits, $fields );
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

/*	public function isActiveJob( $jobDefinitionIdOrIdentifier )
	{
	}*/

/*	public function isLockedJob()
	{
	}*/

	public function prepareManuallyJobRun( object $job ): ?object
	{
		if( $this->isPreparableJob( $job, Model_Job_Run::TYPE_MANUALLY ) ){
			$jobRunId	= $this->modelRun->add( array(
				'jobDefinitionId'	=> $job->jobDefinitionId,
				'processId'			=> getmypid(),
				'type'				=> Model_Job_Run::TYPE_MANUALLY,
				'status'			=> Model_Job_Run::STATUS_PREPARED,
//				'message'			=> json_encode( array() ),
				'createdAt'			=> time(),
				'modifiedAt'		=> time(),
			) );
			return $this->getPreparedJobRun( $jobRunId );
		}
		return NULL;
	}

	public function prepareScheduledJobs( $jobDefinitionId = NULL ): array
	{
		$scheduledJobsToPrepare	= $this->getScheduledJobsToPrepare( $jobDefinitionId );
		$preparedJobs	= $this->prepareJobRunsForScheduledJobs( $scheduledJobsToPrepare );
		return $preparedJobs;
	}

	public function quitJobRun( int $jobRunId, $status, $messageData = array() )
	{
		$jobRun	= $this->modelRun->get( $jobRunId );
		if( (int) $jobRun->status !== Model_Job_Run::STATUS_RUNNING )
			throw new RuntimeException( 'Job is not running' );
		$dataRun	= array(
			'status'		=> $status,
			'modifiedAt'	=> time(),
			'message'		=> json_encode( $messageData ),
		);
		if( $status !== Model_Job_Run::STATUS_TERMINATED )
			$dataRun['finishedAt']	= time();
		$this->modelRun->edit( $jobRun->jobRunId, $dataRun );
		$jobDefinition	= $this->modelDefinition->get( $jobRun->jobDefinitionId );
		$failStatuses	= array(
			Model_Job_Run::STATUS_TERMINATED,
			Model_Job_Run::STATUS_FAILED,
			Model_Job_Run::STATUS_ABORTED,
		);
		$dataDefinition	= array();
		if( in_array( $status, $failStatuses ) )
			$dataDefinition['fails']	= $jobDefinition->fails + 1;
		if( $dataDefinition )
			$this->modelDefinition->edit( $jobRun->jobDefinitionId, $dataDefinition );
	}

	public function startJobRun( object $jobRun, $commands = array(), $parameters = array() ): int
	{
		$this->modelRun->edit( $jobRun->jobRunId, array(
			'status'		=> Model_Job_Run::STATUS_RUNNING,
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
		try{																						//  try to ...
			$result		= \Alg_Object_MethodFactory::call( $jobObject, $methodName, $arguments );	//  ... call job method of job instance with arguments
			$this->quitJobRun( (int) $jobRun->jobRunId, Model_Job_Run::STATUS_DONE, array(			//  finish job run since no exception has been thrown
				'type'		=> 'result',															//  ... and save message of type "result"
				'code'		=> $result,																//  ... containing the method call return code
				'results'	=> $jobObject->getResults(),											//  ... and results collected by the method call
			) );
			if( is_integer( $result ) )																//  method call return value is an integer
				return $result;																		//  carry this return code out as status
			if( strlen( trim( $result ) ) )															//  handle old return strings @deprecated
				foreach( explode( "\n", trim( $result ) ) as $line )								//  handle each result line
					$this->log( $line );															//  by logging
			return 1;																				//  quit with positive status
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
		return -1;																					//  quit with negative status
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
			'jobDefinitionId'	=> $job->jobDefinitionId,
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
		$indicesByMonth	= array_merge( array(
			'status'		=> Model_Job_Schedule::STATUS_ENABLED,
			'monthOfYear'	=> ['*', date( 'n' )],
			'dayOfWeek'		=> [''],
			'dayOfMonth'	=> ['*', date( 'j' )],
			'hourOfDay'		=> ['*', date( 'G' )],
			'minuteOfHour'	=> ['*', date( 'i' )],
		), $jobDefinitionId ? array( 'jobDefinitionId' => $jobDefinitionId ) : array() );
		$indicesByWeek	= array_merge( array(
			'status'		=> Model_Job_Schedule::STATUS_ENABLED,
			'monthOfYear'	=> ['*', date( 'n' )],
			'dayOfWeek'		=> ['*', date( 'N' )],
			'dayOfMonth'	=> [''],
			'hourOfDay'		=> ['*', date( 'G' )],
			'minuteOfHour'	=> ['*', date( 'i' )],
		), $jobDefinitionId ? array( 'jobDefinitionId' => $jobDefinitionId ) : array() );
		$jobsByMonthDays	= $this->modelSchedule->getAllByIndices( $indicesByMonth );
		$jobsByWeekDays		= $this->modelSchedule->getAllByIndices( $indicesByWeek );
		return $jobsByMonthDays + $jobsByWeekDays;
	}

	protected function isPreparableJob( object $job, $runType = 0 ): bool
	{
		if( (int) $job->status !== Model_Job_Definition::STATUS_ENABLED )
			return FALSE;

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
				return FALSE;
		}
		if( (int) $job->mode === Model_Job_Definition::MODE_MULTIPLE )
			return TRUE;
		if( (int) $job->mode === Model_Job_Definition::MODE_EXCLUSIVE ){
			$runningJobRuns	= $this->modelRun->getByIndices( array(
				'status'		=> Model_Job_Run::STATUS_RUNNING,
			) );
			if( $runningJobRuns )		// @todo finish impl: exclude currently "running" job "run scheduled jobs" to make this work
				return FALSE;

		}
		if( (int) $job->mode === Model_Job_Definition::MODE_SINGLE ){
			$runningJob	= $this->modelRun->getByIndices( array(
				'jobDefinitionId'	=> $job->jobDefinitionId,
				'type'			=> $runType,
				'status'		=> Model_Job_Run::STATUS_RUNNING,
			) );
			if( $runningJob )
				return FALSE;
		}
		return TRUE;
	}

	protected function prepareJobRunsForScheduledJobs( $scheduledJobsToPrepare ): array
	{
		$list	= array();
		foreach( $scheduledJobsToPrepare as $job ){
			$date		= date( 'Y-m-d-H-i' );
			if( $this->isPreparableJob( $job ) ){
				$this->abortPreparedJobRuns( $job->jobDefinitionId );
				$jobRunId	= $this->modelRun->add( array(
					'jobScheduleId'	=> $job->jobScheduleId,
					'jobDefinitionId'	=> $job->jobDefinitionId,
					'type'			=> Model_Job_Run::TYPE_SCHEDULED,
					'status'		=> Model_Job_Run::STATUS_PREPARED,
//					'message'		=> json_encode( array() ),
					'createdAt'		=> time(),
					'modifiedAt'	=> time(),
				) );
				$list[$jobRunId]	= $this->modelRun->get( $jobRunId );
			}
		}
		return $list;
	}
}
