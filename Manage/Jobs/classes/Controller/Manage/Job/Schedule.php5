<?php
class Controller_Manage_Job_Schedule extends CMF_Hydrogen_Controller
{
	protected $modelSchedule;
	protected $modelDefinition;
	protected $modelRun;
	protected $logic;

	public function add()
	{
		if( $this->request->getMethod()->isPost() ){
			$data	= $this->request->getAll();
			$jobScheduleId	= $this->modelSchedule->add( $data );
			$this->restart( 'edit/'.$jobScheduleId, TRUE );
		}
	}

	public function edit( $jobScheduleId )
	{
		if( $this->request->getMethod()->isPost() ){

		}
	}

	public function index( $page = 0 )
	{
		$schedule		= $this->modelSchedule->getAll( array(), array() );
		foreach( $schedule as $item ){
			$item->definition	= $this->allDefinitions[(int) $item->jobDefinitionId];
		}
		$this->addData( 'allDefinedJobs', $this->allDefinitions );
		$this->addData( 'scheduledJobs', $schedule );
	}

	public function remove( $jobScheduleId )
	{
	}

	public function setStatus( $jobScheduleId, $status )
	{
		$from	= $this->request->get( 'from' );
		$this->modelSchedule->edit( $jobScheduleId, array(
			'status'		=> $status,
			'modifiedAt'	=> time(),
		) );
		$this->restart( $from ? $from : NULL, !$from );
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		$this->request			= $this->env->getRequest();
		$this->modelDefinition	= new Model_Job_Definition( $this->env );
		$this->modelSchedule	= new Model_Job_Schedule( $this->env );
		$this->modelRun			= new Model_Job_Run( $this->env );
		$this->logic			= $this->env->getLogic()->get( 'Job' );

		$this->allDefinitions	= array();
		$definitions	= $this->modelDefinition->getAll( array(), array( 'identifier' => 'ASC' ) );
		foreach( $definitions as $definition )
			$this->allDefinitions[(int) $definition->jobDefinitionId] = $definition;

		$this->addData( 'wordsGeneral', $this->env->getLanguage()->getWords( 'manage/job' ) );
	}
}
