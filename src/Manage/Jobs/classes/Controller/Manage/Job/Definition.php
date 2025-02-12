<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Job_Definition extends Controller
{
	protected HttpRequest $request;
	protected Dictionary $session;
	protected Model_Job_Definition $modelDefinition;
	protected Model_Job_Run $modelRun;
	protected Model_Job_Schedule $modelSchedule;
	protected Model_Job_Code $modelCode;
	protected Logic_Job $logic;
	protected string $filterPrefix			= 'filter_manage_job_definition_';

	public function filter( $reset = NULL ): void
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

	public function index( int $page = 0 ): void
	{
		$filterLimit	= $this->session->get( $this->filterPrefix.'limit' ) ?? 10;
		$filterStatus	= trim( $this->session->get( $this->filterPrefix.'status', '' ) );
		$filterMode		= trim( $this->session->get( $this->filterPrefix.'mode', '' ) );
		$filterClass	= trim( $this->session->get( $this->filterPrefix.'class', '' ) );
		$filterMethod	= trim( $this->session->get( $this->filterPrefix.'method', '' ) );

		$conditions		= [];
		if( '' !== $filterStatus )
			$conditions['status']		= $filterStatus;
		if( '' !== $filterMode )
			$conditions['mode']			= $filterMode;
		if( '' !== $filterClass )
			$conditions['className']	= $filterClass;
		if( '' !== $filterMethod )
			$conditions['methodName']	= $filterMethod;

		$total	= $this->modelDefinition->count( $conditions );
		$orders	= ['identifier' => 'ASC'];
		$limits	= [$page * $filterLimit, $filterLimit];
		$definitions	= $this->modelDefinition->getAll( $conditions, $orders, $limits );
		foreach( $definitions as $item )
			$item->scheduled	= $this->modelSchedule->getAllByIndex( 'jobDefinitionId', $item->jobDefinitionId );

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

	/**
	 *	@param		int|string		$jobDefinitionId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function view( int|string $jobDefinitionId ): void
	{
		$definition	= $this->modelDefinition->get( $jobDefinitionId );
		if( !$definition ){
			$this->env->getMessenger()->noteError( 'Invalid Job Definition ID.' );
			$this->restart( NULL, TRUE );
		}

		try{
			$this->modelCode->readFile( 'classes/Job/'.str_replace( '_', '/', $definition->className ).'.php' );
			$definitionCode	= $this->modelCode->getClassMethodSourceCode( 'Job_'.$definition->className, $definition->methodName );
			$this->addData( 'definitionCode', $definitionCode );
		}
		catch( Throwable $e ){
			$this->env->getLog()?->logException( $e );
			$this->addData( 'definitionCode', NULL );
		}

		$runs	= $this->modelRun->getAllByIndex( 'jobDefinitionId', $jobDefinitionId, ['createdAt' => 'DESC'], [0, 10] );
		$this->addData( 'definition', $definition );
		$this->addData( 'runs', $runs );
//		print_m( $definition );
//		print_m( $runs );
//		print( $runList );
//		die;
	}

	/**
	 *	@param		int|string		$jobDefinitionId
	 *	@param		$status
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setStatus( int|string $jobDefinitionId, $status ): void
	{
		$from	= $this->request->get( 'from' );
		$this->modelDefinition->edit( $jobDefinitionId, [
			'status'		=> $status,
			'modifiedAt'	=> time(),
		] );
		$this->restart( $from ?: NULL, !$from );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->modelDefinition	= new Model_Job_Definition( $this->env );
		$this->modelSchedule	= new Model_Job_Schedule( $this->env );
		$this->modelRun			= new Model_Job_Run( $this->env );
		$this->modelCode		= new Model_Job_Code( $this->env );
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logic			= $this->env->getLogic()->get( 'Job' );
		$this->addData( 'wordsGeneral', $this->env->getLanguage()->getWords( 'manage/job' ) );
	}
}
