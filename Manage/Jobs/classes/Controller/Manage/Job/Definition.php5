<?php
class Controller_Manage_Job_Definition extends CMF_Hydrogen_Controller
{
	protected $modelDefinition;
	protected $modelRun;
	protected $modelSchedule;
	protected $logic;

	public function index()
	{
		$definitions	= $this->modelDefinition->getAll( array(), array( 'identifier' => 'ASC' ) );
		foreach( $definitions as $item ){
			$item->scheduled	= $this->modelSchedule->getAllByIndex( 'jobDefinitionId', $item->jobDefinitionId );
		}
		$this->addData( 'definitions', $definitions );
	}

	public function setStatus( $jobDefinitionId, $status )
	{
		$from	= $this->request->get( 'from' );
		$this->modelDefinition->edit( $jobDefinitionId, array(
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
	}
}
