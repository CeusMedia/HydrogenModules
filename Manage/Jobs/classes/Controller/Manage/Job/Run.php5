<?php
class Controller_Manage_Job_Run extends CMF_Hydrogen_Controller
{
	protected $request;
	protected $session;
	protected $modelDefinition;
	protected $modelRun;
	protected $modelSchedule;
	protected $logic;
	protected $filterPrefix			= 'filter_manage_job_run_';

	public function filter( $reset = NULL )
	{
		if( $reset ){
			$this->session->remove( $this->filterPrefix.'limit' );
			$this->session->remove( $this->filterPrefix.'status' );
			$this->session->remove( $this->filterPrefix.'type' );
			$this->session->remove( $this->filterPrefix.'jobId' );
		}
//		if( $this->request->has( 'status' ) )
		$this->session->set( $this->filterPrefix.'status', $this->request->get( 'status' ) );
		$this->session->set( $this->filterPrefix.'type', $this->request->get( 'type' ) );
		$this->session->set( $this->filterPrefix.'jobId', $this->request->get( 'jobId' ) );
		$this->restart( NULL, TRUE );
	}

	public function index( $page = 0 )
	{
		$definitionMap	= array();
		$definitions	= $this->modelDefinition->getAll( array(), array( 'identifier' => 'ASC' ) );
		foreach( $definitions as $definition )
			$definitionMap[$definition->jobDefinitionId]	= $definition;

		$filterLimit	= $this->session->get( $this->filterPrefix.'limit' ) ?? 15;
		$filterStatus	= $this->session->get( $this->filterPrefix.'status' );
		$filterType		= $this->session->get( $this->filterPrefix.'type' );
		$filterJobId	= $this->session->get( $this->filterPrefix.'jobId' );

		if( $filterStatus === Model_Job_Run::STATUSES )
			$filterStatus	= array();

		$conditions	= array();
		if( $filterStatus && count( $filterStatus ) )
			$conditions['status']		= $filterStatus;
		if( in_array( $filterType, Model_Job_Run::TYPES ) )
			$conditions['type']			= $filterType;
		if( $filterJobId )
			$conditions['jobDefinitionId']		= $filterJobId;

		$total		= $this->modelRun->count( $conditions );
		while( ceil( $total / $filterLimit ) <= $page )
			$page--;

		$orders	= array( 'createdAt' => 'DESC' );
		$limits	= array( $page * $filterLimit, $filterLimit );
		$runs	= $this->modelRun->getAll( $conditions, $orders, $limits );

		$this->addData( 'definitions', $definitionMap );
		$this->addData( 'runs', $runs );
		$this->addData( 'filterLimit', $filterLimit );
		$this->addData( 'filterStatus', $filterStatus );
		$this->addData( 'filterType', $filterType );
		$this->addData( 'filterJobId', $filterJobId );
		$this->addData( 'total', $total );
		$this->addData( 'page', $page );

	}

	public function view( $jobDefinitionId )
	{
//		print_m( $definition );
//		print_m( $runs );
//		print( $runList );
//		die;
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->modelDefinition	= new Model_Job_Definition( $this->env );
		$this->modelSchedule	= new Model_Job_Schedule( $this->env );
		$this->modelRun			= new Model_Job_Run( $this->env );
		$this->modelCode		= new Model_Job_Code( $this->env );
		$this->logic			= $this->env->getLogic()->get( 'Job' );
		$this->addData( 'wordsGeneral', $this->env->getLanguage()->getWords( 'manage/job' ) );
	}
}
