<?php
/**
 *	This job exists to training working with jobs, only.
 *	Nothing productive will be done.
 *	Since jobs can be configured by call using commands and parameters,
 *	you can use these jobs to learn to use these options.
 */
class Job_Job_Schedule extends Job_Abstract
{
//	protected $pathLocks	= 'config/locks/';
//	protected $pathJobs		= 'config/jobs/';
//	protected $logic;

	public function archive()
	{
		$identifiers	= array(
			'Job.Schedule.run',
			'Job.Schedule.archive',
			'Job.Test.wait',
		);
		$modelDefinition	= new Model_Job_Definition( $this->env );
		$modelRun			= new Model_Job_Run( $this->env );

		$conditions		= array( 'identifier' => $identifiers );
		$fields			= array( 'jobDefinitionId' );
		$definitionIds	= $modelDefinition->getAll( $conditions, array(), array(), $fields );

		$conditions		= array(
			'jobDefinitionId'	=> $definitionIds,
			'status'			=> array(
				Model_Job_Run::STATUS_DONE,
				Model_Job_Run::STATUS_SUCCESS,
			)
		);
		$runIds		= $modelRun->getAll( $conditions, array(), array(), array( 'jobRunId' ) );
		foreach( $runIds as $runId )
			$this->logic->archiveJobRun( $runId );
		$nrJobs	= count( $runIds );
		$this->results	= array( 'count' => $nrJobs );
		$this->out( sprintf( 'Archived %d job runs.', $nrJobs ) );
		return $nrJobs ? 2 : 1;
	}

	public function run()
	{
		$preparedJobs	= $this->logic->prepareScheduledJobs();
		$numberFound	= count( $preparedJobs );
		$numberRan		= 0;
		$numberDone		= 0;
		foreach( $preparedJobs as $preparedJobRun ){
			try{
				$args					= $preparedJobRun->arguments;
				$commands				= array();
				$parameters				= array();
				$fallBackOnEmptyPair	= FALSE;
				if( strlen( trim( $preparedJobRun->arguments ) ) ){
					$args	= preg_split( '/ +/', trim( $preparedJobRun->arguments ) );
					foreach( $args as $argument ){
						if( substr_count( $argument, '=' ) || $fallBackOnEmptyPair ){
							$parts	= explode( '=', $argument, 2 );
							$key	= array_shift( $parts );
							$value	= $parts ? $parts[0] : NULL;
							$parameters[$key]	= $value;
						}
						else
							$commands[]	= $argument;
					}
				}
				$result		= $this->logic->startJobRun( $preparedJobRun, $commands, $parameters );
				$numberRan++;
				if( $result === 1 )
					$numberDone++;
			}
			catch( \Exception $e ){
			}
		}
		$this->results	= array(
			'numberFound'	=> $numberFound,
			'numberRan'		=> $numberRan,
			'numberdone'	=> $numberDone,
		);
		return 1;
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		$this->logic	= $this->env->getLogic()->get( 'Job' );
/*		$this->skipJobs	= array(
			$this->logic->getDefinitionByIdentifier( 'Job.Lock.clear' )->jobDefinitionId,
			$this->logic->getDefinitionByIdentifier( 'Job.Lock.list' )->jobDefinitionId,
		);*/
	}
}
