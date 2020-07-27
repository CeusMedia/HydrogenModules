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

	public function abort( $jobRunId )
	{
		$jobRun	= $this->modelRun->get( $jobRunId );
		if( (int) $jobRun->status !== Model_Job_Run::STATUS_PREPARED ){
			$msg	= 'Der Job konnte nicht mehr verhindert werden.';
			$title	= $jobRun->title;
			if( !$title )
				$title	= $this->modelDefinition->get( $jobRun->jobDefinitionId, 'identifier' );
			$this->env->getMessenger()->noteError( sprintf( $msg, $title ) );
		}
		else{
			$this->modelRun->edit( $jobRunId, array(
				'status'		=> Model_Job_Run::STATUS_ABORTED,
				'modifiedAt'	=> time(),
			) );
		}
		$from	= $this->request->get( 'from' );
		$this->restart( $from, !$from );
	}

	public function archive( $jobRunId )
	{
		$this->logic->archiveJobRun( $jobRunId );
		$from	= $this->request->get( 'from' );
		$this->restart( $from, !$from );
	}

	public function filter( $reset = NULL )
	{
		$filters	= array(
			'limit',
			'status',
			'type',
			'jobId',
			'startFrom',
			'startTo',
		);
		if( $reset ){
			foreach( $filters as $filterKey )
				$this->session->remove( $this->filterPrefix.$filterKey );
		}
		foreach( $filters as $filterKey ){
			$value	= $this->compactFilterInput( $this->request->get( $filterKey ) );
			$this->session->set( $this->filterPrefix.$filterKey, $value );
		}
/*print_m( $this->request->getAll() );
print_m( $this->session->getAll( $this->filterPrefix ) );
die;*/
		$this->restart( NULL, TRUE );
	}

	public function index( $page = 0 )
	{
		$definitionMap	= array();
		$definitions	= $this->modelDefinition->getAll( array(), array( 'identifier' => 'ASC' ) );
		foreach( $definitions as $definition )
			$definitionMap[$definition->jobDefinitionId]	= $definition;

		$filterLimit		= $this->session->get( $this->filterPrefix.'limit' ) ?? 15;
		$filterStatus		= $this->session->get( $this->filterPrefix.'status' );
		$filterType			= $this->session->get( $this->filterPrefix.'type' );
		$filterJobId		= $this->session->get( $this->filterPrefix.'jobId' );
		$filterStartFrom	= $this->session->get( $this->filterPrefix.'startFrom' );
		$filterStartTo		= $this->session->get( $this->filterPrefix.'startTo' );
		$filterArchived		= $this->session->get( $this->filterPrefix.'archived' );

		if( $filterStatus === Model_Job_Run::STATUSES )
			$filterStatus	= array();

		$conditions	= array(
			'archived'	=> (int)$filterArchived,
		);
		if( is_array( $filterStatus ) && count( $filterStatus ) )
			$conditions['status']		= $filterStatus;
		if( strlen( $filterType ) && in_array( $filterType, Model_Job_Run::TYPES ) )
			$conditions['type']			= $filterType;
		if( $filterJobId )
			$conditions['jobDefinitionId']		= $filterJobId;

		if( $filterStartFrom || $filterStartTo ){
			if( $filterStartFrom ){
				$timestampStart	= strtotime( $filterStartFrom.' 00:00:00' );
				$conditions['ranAt']		= '>= '.$timestampStart;
			}
			if( $filterStartTo ){
				$timestampTo	= strtotime( $filterStartTo.' 23:59:59' );
				$conditions['ranAt']		= '<= '.$timestampTo;
			}
			if( $filterStartFrom && $filterStartTo )
				$conditions['ranAt']		= '>< '.$timestampStart.' & '.$timestampTo;
		}

		$total		= $this->modelRun->count( $conditions );
		while( ceil( $total / $filterLimit ) <= $page )
			$page--;

		$orders	= array( 'createdAt' => 'DESC', 'jobRunId' => 'DESC' );
		$limits	= array( $page * $filterLimit, $filterLimit );
		$runs	= $this->modelRun->getAll( $conditions, $orders, $limits );

		$this->addData( 'definitions', $definitionMap );
		$this->addData( 'runs', $runs );
		$this->addData( 'filterLimit', $filterLimit );
		$this->addData( 'filterStatus', $filterStatus );
		$this->addData( 'filterType', $filterType );
		$this->addData( 'filterJobId', $filterJobId );
		$this->addData( 'filterStartFrom', $filterStartFrom );
		$this->addData( 'filterStartTo', $filterStartTo );
		$this->addData( 'total', $total );
		$this->addData( 'page', $page );
	}

	public function terminate( $jobRunId )
	{
		$jobRun	= $this->modelRun->get( $jobRunId );
		if( (int) $jobRun->status !== Model_Job_Run::STATUS_RUNNING ){
			$msg	= 'Der Job "%s" konnte nicht mehr abgebrochen werden.';
			$title	= $jobRun->title;
			if( !$title )
				$title	= $this->modelDefinition->get( $jobRun->jobDefinitionId, 'identifier' );
			$this->env->getMessenger()->noteError( sprintf( $msg, $title ) );
		}
		else{
			$this->modelRun->edit( $jobRunId, array(
				'status'		=> Model_Job_Run::STATUS_TERMINATED,
				'modifiedAt'	=> time(),
				'finishedAt'	=> time(),
			) );
		}
		$from	= $this->request->get( 'from' );
		$this->restart( $from, !$from );
	}

	public function view( $jobRunId )
	{
		$jobRun			= $this->modelRun->get( $jobRunId );
		$jobDefinition	= $this->modelDefinition->get( $jobRun->jobDefinitionId );
		$jobSchedule	= NULL;
		if( $jobRun->jobScheduleId ){
			$jobSchedule	= $this->modelSchedule->get( $jobRun->jobScheduleId );
		}

		$this->addData( 'run', $jobRun );
		$this->addData( 'definition', $jobDefinition );
		$this->addData( 'schedule', $jobSchedule );
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
