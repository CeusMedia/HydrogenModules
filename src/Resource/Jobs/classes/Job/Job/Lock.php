<?php
class Job_Job_Lock extends Job_Abstract
{
	protected Logic_Job $logic;
	private array $skipJobs;

	/**
	 *	@todo		finish implementation
	 */
	public function alert(){
	}

	public function clear( $jobIds = [], $reason = NULL ): void
	{
		$list	= [];
		foreach( $this->getLockedJobs() as $runningJob ){
			$messageData	= $reason ? ['reason' => $reason] : [];
			$this->logic->quitJobRun( (int) $runningJob->jobRunId, Model_Job_Run::STATUS_TERMINATED, $messageData );
			$list[]	= (object) array(
				'jobRunId'			=> $runningJob->jobRunId,
				'jobDefinitionId'	=> $runningJob->jobDefinitionId,
				'jobScheduleId'		=> $runningJob->jobScheduleId,
				'jobDefinition'		=> $this->logic->getDefinition( $runningJob->jobDefinitionId ),
			);
		}
		$this->results	= $list;
		$this->out( 'Removed '.count( $list ).' locks'.( $list ? ':' : '.' ) );
		foreach( $list as $item )
			$this->out( ' - '.$item->jobDefinition->identifier.' (Run ID: '.$item->jobRunId.')' );
		$list ? $this->out() : NULL;
	}

	public function list(): void
	{
		$list	= [];
		foreach( $this->getLockedJobs() as $runningJob ){
			$list[]	= (object) array(
				'jobRunId'			=> $runningJob->jobRunId,
				'jobDefinitionId'	=> $runningJob->jobDefinitionId,
				'jobScheduleId'		=> $runningJob->jobScheduleId,
				'jobDefinition'		=> $this->logic->getDefinition( $runningJob->jobDefinitionId ),
			);
		}
		$this->results	= $list;
		$this->out( 'Found '.count( $list ).' locks'.( $list ? ':' : '.' ) );
		foreach( $list as $item )
			$this->out( ' - '.$item->jobDefinition->identifier.' (Run ID: '.$item->jobRunId.')' );
		$list ? $this->out() : NULL;
	}

	//  --  PROTECTED  --  //

	protected function __onInit(): void
	{
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logic	= $this->env->getLogic()->get( 'Job' );
		$this->skipJobs	= array(
			$this->logic->getDefinitionByIdentifier( 'Job.Lock.clear' )->jobDefinitionId,
			$this->logic->getDefinitionByIdentifier( 'Job.Lock.list' )->jobDefinitionId,
		);
	}

	protected function getLockedJobs(): array
	{
		$runningJobs	= $this->logic->getRunningJobs( [], ['ranAt' => 'ASC'] );
		$list			= [];
		foreach( $runningJobs as $runningJob )
			if( !in_array( $runningJob->jobDefinitionId, $this->skipJobs ) )
				$list[]	= $runningJob;
		return $list;
	}
}
