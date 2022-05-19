<?php
class Controller_Manage_Job_Definition extends CMF_Hydrogen_Controller
{
	protected $request;
	protected $session;
	protected $modelDefinition;
	protected $modelRun;
	protected $modelSchedule;
	protected $logic;
	protected $filterPrefix			= 'filter_manage_job_definition_';

	public function filter( $reset = NULL )
	{
		if( $reset ){
			$this->session->remove( $this->filterPrefix.'limit' );
			$this->session->remove( $this->filterPrefix.'status' );
			$this->session->remove( $this->filterPrefix.'mode' );
			$this->session->remove( $this->filterPrefix.'class' );
			$this->session->remove( $this->filterPrefix.'method' );
		}
//		if( $this->request->has( 'status' ) )
		$this->session->set( $this->filterPrefix.'status', $this->request->get( 'status' ) );
		$this->session->set( $this->filterPrefix.'mode', $this->request->get( 'mode' ) );
		$this->session->set( $this->filterPrefix.'class', $this->request->get( 'class' ) );
		$this->session->set( $this->filterPrefix.'method', $this->request->get( 'method' ) );
		$this->restart( NULL, TRUE );
	}

	public function index( $page = 0 )
	{
		$filterLimit	= $this->session->get( $this->filterPrefix.'limit' ) ?? 10;
		$filterStatus	= $this->session->get( $this->filterPrefix.'status' );
		$filterMode		= $this->session->get( $this->filterPrefix.'mode' );
		$filterClass	= $this->session->get( $this->filterPrefix.'class' );
		$filterMethod	= $this->session->get( $this->filterPrefix.'method' );

		$conditions		= [];
		if( strlen( $filterStatus ) )
			$conditions['status']		= $filterStatus;
		if( strlen( $filterMode ) )
			$conditions['mode']			= $filterMode;
		if( strlen( $filterClass ) )
			$conditions['className']	= $filterClass;
		if( strlen( $filterMethod ) )
			$conditions['methodName']	= $filterMethod;

		$total	= $this->modelDefinition->count( $conditions );
		$orders	= array( 'identifier' => 'ASC' );
		$limits	= array( $page * $filterLimit, $filterLimit );
		$definitions	= $this->modelDefinition->getAll( $conditions, $orders, $limits );
		foreach( $definitions as $item ){
			$item->scheduled	= $this->modelSchedule->getAllByIndex( 'jobDefinitionId', $item->jobDefinitionId );
		}

		$classNames		= [];
		$methodNames	= [];
		foreach( $this->modelDefinition->getAll() as $definition ){
			if( !in_array( $definition->className, $classNames ) )
				$classNames[]	= $definition->className;
			if( !in_array( $definition->methodName, $methodNames ) )
				$methodNames[]	= $definition->methodName;
		}
		natcasesort( $classNames );
		natcasesort( $methodNames );

		$this->addData( 'definitions', $definitions );
		$this->addData( 'classNames', $classNames );
		$this->addData( 'methodNames', $methodNames );
		$this->addData( 'filterLimit', $filterLimit );
		$this->addData( 'filterStatus', $filterStatus );
		$this->addData( 'filterMode', $filterMode );
		$this->addData( 'filterClass', $filterClass );
		$this->addData( 'filterMethod', $filterMethod );
		$this->addData( 'total', $total );
		$this->addData( 'page', $page );
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
		$this->session			= $this->env->getSession();
		$this->modelDefinition	= new Model_Job_Definition( $this->env );
		$this->modelSchedule	= new Model_Job_Schedule( $this->env );
		$this->modelRun			= new Model_Job_Run( $this->env );
		$this->modelCode		= new Model_Job_Code( $this->env );
		$this->logic			= $this->env->getLogic()->get( 'Job' );
		$this->addData( 'wordsGeneral', $this->env->getLanguage()->getWords( 'manage/job' ) );
	}
}
