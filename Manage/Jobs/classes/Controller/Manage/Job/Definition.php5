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

	public function view( $jobDefinitionId )
	{
		$definition	= $this->modelDefinition->get( $jobDefinitionId );
		if( !$definition ){
			$this->env->getMessenger()->noteError( 'Invalid Job Definition ID.' );
			$this->restart( NULL, TRUE );
		}
		$this->modelCode->readFile( 'classes/Job/'.str_replace( '_', '/', $definition->className ).'.php5' );
		$definitionCode	= $this->modelCode->getClassMethodSourceCode( 'Job_'.$definition->className, $definition->methodName );
		$runs	= $this->modelRun->getAllByIndex( 'jobDefinitionId', $jobDefinitionId, array( 'createdAt' => 'DESC' ), array( 0, 10 ) );
		$this->addData( 'definition', $definition );
		$this->addData( 'runs', $runs );
		$this->addData( 'definitionCode', $definitionCode );
//		print_m( $definition );
//		print_m( $runs );
//		print( $runList );
//		die;
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
		$this->modelCode		= new Model_Job_Code( $this->env );
		$this->logic			= $this->env->getLogic()->get( 'Job' );
	}
}
