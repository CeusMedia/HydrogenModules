<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Work_Time_Archive extends Controller
{
	protected $request;
	protected $session;
	protected $userId;
	protected $modelProject;
	protected $modelMission;
	protected $modelTimer;
	protected $logicProject;
	protected $projectMap;

/*	public function add()
	{
		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAll();
			$data['userId']		= $this->userId;
			$data['createdAt']	= time();
			$data['modifiedAt']	= time();
			$this->modelTimer->add( $data );
			$this->restart( 'add', TRUE );
		}
		$this->addData( 'projectMap', $this->projectMap );
	}*/

	public function edit( $timerId )
	{
		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAll();
			$this->restart( 'archive', TRUE );
		}
		$this->addData( 'activityMap', $this->activityMap );
		$this->addData( 'projectMap', $this->projectMap );
	}

	public function filter()
	{
		$this->session->set( 'filter_work_timer_activity', trim( $this->request->get( 'activity' ) ) );
		$this->session->set( 'filter_work_timer_projectId', $this->request->get( 'projectId' ) );
		$this->session->set( 'filter_work_timer_status', $this->request->get( 'status' ) );
		$this->restart( NULL, TRUE );
	}

	public function index( $limit = 10, $page = 0 )
	{
		$filterQuery		= $this->session->get( 'filter_work_timer_activity' );
		$filterProjectId	= $this->session->get( 'filter_work_timer_projectId' );
		$filterStatus		= $this->session->get( 'filter_work_timer_status' );

		$conditions		= [];
		$conditions['projectId']	= array_keys( $this->projectMap );
		if( strlen( $filterQuery ) )
			$conditions['title']	= '%'.$filterQuery.'%';
		if( strlen( $filterProjectId ) && array_key_exists( $filterProjectId, $this->projectMap ) )
			$conditions['projectId']	= $filterProjectId;
		if( strlen( $filterStatus ) )
			$conditions['status']	= $filterStatus;

		$total		= $this->modelTimer->count( $conditions );
		$timers		= $this->modelTimer->getAll( $conditions, ['modifiedAt' => 'ASC'], [$page * $limit, $limit] );
		$this->addData( 'timers', $timers );
		$this->addData( 'limit', $limit );
		$this->addData( 'page', $page );
		$this->addData( 'total', $total );
//		$this->addData( 'activityMap', $this->activityMap );
		$this->addData( 'projectMap', $this->projectMap );
		$this->addData( 'filterQuery', $filterQuery );
		$this->addData( 'filterProjectId', $filterProjectId );
		$this->addData( 'filterStatus', $filterStatus );
	}

	//  --  PROTECTED  --  //

	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->userId			= $this->session->get( 'auth_user_id' );
		if( !$this->userId ){
			$this->env->getMessenger()->noteError( 'You need to be logged in to use this feature.' );
			$this->restart();
		}
		$this->modelProject		= new Model_Project( $this->env );
		$this->modelMission		= new Model_Mission( $this->env );
		$this->modelTimer		= new Model_Work_Timer( $this->env );
		$this->logicProject		= Logic_Project::getInstance( $this->env );
		$this->projectMap		= $this->logicProject->getUserProjects( $this->userId, TRUE );
//		$this->addData( 'filterProjectId', $this->session->get( 'filter_work_timer_projectId' ) );
//		$this->addData( 'filterStatus', (int) $this->session->get( 'filter_work_timer_status' ) );
		$this->addData( 'userId', $this->userId );
	}
}
