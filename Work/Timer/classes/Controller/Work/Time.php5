<?php
class Controller_Work_Time extends CMF_Hydrogen_Controller
{
	protected $request;
	protected $session;
	protected $messenger;
	protected $userId;
	protected $logicTimer;
	protected $logicProject;
	protected $modelTimer;
	protected $projectMap;
	protected $modules			= [];

	public function add()
	{
		if( !$this->projectMap && !$this->env->getRequest()->isAjax() )
			$this->restart( './manage/project/add?from=work/time/add' );

		if( $this->request->has( 'save' ) ){
			$module			= (string) $this->request->get( 'module' );
			$moduleId		= (int) $this->request->get( 'moduleId' );
			$projectId		= $this->request->get( 'projectId' );
			$title			= $this->request->get( 'title' );
			$desc			= $this->request->get( 'description' );
			$timePlanned	= $this->request->get( 'time_planned' );
			$timeNeeded		= $this->request->get( 'time_needed' );
			$secondsPlanned	= View_Helper_Work_Time::parseTime( $timePlanned );
			$secondsNeeded	= View_Helper_Work_Time::parseTime( $timeNeeded );

			$data		= array(
				'projectId'			=> $projectId,
				'module'			=> $module,
				'moduleId'			=> $moduleId,
				'userId'			=> $this->userId,
				'workerId'			=> $this->request->get( 'workerId' ),
				'title'				=> $this->request->get( 'title' ),
				'description'		=> $this->request->get( 'description' ),
				'secondsPlanned'	=> $secondsPlanned,
				'secondsNeeded'		=> $secondsNeeded,
				'status'			=> 0,
				'createdAt'			=> time(),
				'modifiedAt'		=> time(),
			);

			$timerId	= $this->modelTimer->add( $data );
			$this->messenger->noteSuccess( 'Timer saved.' );
			$status		= (int) $this->request->get( 'status' );
			if( $status === 1 )
				$this->logicTimer->start( $timerId );
			else if( !in_array( $status, array( 0, 1 ) ) )
				$this->modelTimer->edit( $timerId, array( 'status' => $status ) );
			if( $this->request->get( 'from' ) )
				$this->restart( $this->request->get( 'from' ) );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'projectMap', $this->projectMap );
		$this->addData( 'defaultProjectId', $this->logicProject->getDefaultProject( $this->userId ) );
		$this->addData( 'defaultStatus', (int) $this->request->get( 'status' ) );
		$this->addData( 'from', $this->request->get( 'from' ) );
		$this->addData( 'workers', $this->logicProject->getCoworkers( $this->userId ) );
	}

	public function assign()
	{
		$module		= $this->request->get( 'module' );
		$moduleId	= $this->request->get( 'moduleId' );
		$timerIds	= $this->request->get( 'timerIds' );

		if( !$module || !$moduleId ){
			$this->messenger->noteError( 'No valid module relation given.' );
		}
		else{
			$count	= 0;
			foreach( $timerIds as $timerId ){
				$count		+= $this->modelTimer->edit( $timerId, array(
					'module'	=> $module,
					'moduleId'	=> $moduleId,
				) );
			}
			$this->messenger->noteSuccess( '%d Timer(s) assigned.', $count );
		}
		if( $this->request->get( 'from' ) )
			$this->restart( $this->request->get( 'from' ) );
		$this->restart( NULL, TRUE );
	}

	public function edit( $timerId )
	{
		$timer	= $this->modelTimer->get( $timerId );
		if( !$timer ){
			$this->messenger->noteError( 'Invalid timer ID' );
			$this->restart( NULL, TRUE );
		}
		View_Helper_Work_Time_Timer::decorateTimer( $this->env, $timer, FALSE );

		if( $this->request->has( 'save' ) ){
			$data			= [];
			if( $timer->status != 1 )
				$data['modifiedAt'] = time();
			if( $this->request->has( 'workerId' ) )
				$data['workerId']		= $this->request->get( 'workerId' );
			if( $this->request->has( 'title' ) )
				$data['title']			= $this->request->get( 'title' );
			if( $this->request->has( 'description' ) )
				$data['description']	= $this->request->get( 'description' );
			if( $this->request->has( 'time_planned' ) ){
				$timePlanned	= $this->request->get( 'time_planned' );
				$secondsPlanned	= View_Helper_Work_Time::parseTime( $timePlanned );
				$data['secondsPlanned']	= $secondsPlanned;
			}
			if( $this->request->has( 'time_needed' ) ){
				$timeNeeded		= $this->request->get( 'time_needed' );
				$secondsNeeded	= View_Helper_Work_Time::parseTime( $timeNeeded );
				$data['secondsNeeded']	= $secondsNeeded;
			}

			$this->modelTimer->edit( $timerId, $data, FALSE );

			if( $this->request->has( 'status' ) ){
				$newStatus		= (int) $this->request->get( 'status' );
				if( $newStatus !== (int) $timer->status ){											//  timer status has changed
	//				print_m( $newStatus );
	//				print_m( (int) $timer->status );
	//				die;
					if( $newStatus === 1 )															//  to start
						$this->logicTimer->start( $timerId );										//  handle change by logic (stop others)
					else if( $newStatus === 2 )														//  to pause
						$this->logicTimer->pause( $timerId );										//  handle change by logic (do math)
					else if( $newStatus === 3 )														//  to stop
						$this->logicTimer->stop( $timerId );										//  handle change by logic (calculate times)
					else if( $newStatus === 0 )														//  to new
						$this->modelTimer->edit( $timerId, array(									//  edit timer by resetting
							'secondsNeeded'	=> 0,													//  needed time to 0
						) );
				}
			}
			$this->messenger->noteSuccess( 'Timer saved.' );
			if( $this->request->get( 'from' ) )
				$this->restart( $this->request->get( 'from' ) );
			$this->restart( 'edit/'.$timerId, TRUE );
		}
//		$this->addData( 'activityMap', $this->activityMap );
		$this->addData( 'projectMap', $this->projectMap );
		$this->addData( 'timer', $timer );
		$this->addData( 'from', $this->request->get( 'from' ) );
		$this->addData( 'timerId', $timerId );

        $logicAuth      = Logic_Authentication::getInstance( $this->env );
        $currentUserId  = $logicAuth->getCurrentUserId();
		$projectUsers	= [];
		if( $timer->projectId ){
	        $logicProject   = Logic_Project::getInstance( $this->env );
			$projectUsers	= $logicProject->getProjectUsers( $timer->projectId, array(), array( 'username' => 'ASC' ) );
			if( !$timer->workerId )
				$timer->workerId	= $currentUserId;
		}
		$this->addData( 'projectUsers', $projectUsers );

	}

	public function filter()
	{
		$this->session->set( 'filter_work_timer_activityId', $this->request->get( 'activityId' ) );
		$this->session->set( 'filter_work_timer_projectId', $this->request->get( 'projectId' ) );
		$this->session->set( 'filter_work_timer_status', $this->request->get( 'status' ) );
		$this->restart( NULL, TRUE );
	}

	public function index( $limit = 10, $page = 0 )
	{
		if( !$this->projectMap && !$this->env->getRequest()->isAjax() )
			$this->restart( './manage/project/add?from=work/time' );

		$conditions	= array( 'status' => array( 2 ) );
		$total		= $this->modelTimer->count( $conditions );
		$timers		= $this->modelTimer->getAll( $conditions, array( 'modifiedAt' => 'ASC' ), array( $page * $limit, $limit ) );
		foreach( $timers as $timer ){
			View_Helper_Work_Time_Timer::decorateTimer( $this->env, $timer, FALSE );
		}
		$conditions	= array(
			'userId'	=> (int) $this->userId,
			'status'	=> 1,
		);
		$timer		= $this->modelTimer->getByIndices( $conditions );
		if( $timer )
			View_Helper_Work_Time_Timer::decorateTimer( $this->env, $timer );

		$conditions	= array(
			'moduleId'	=> 0,
			'userId'	=> $this->userId
		);
		$unrelatedTimers	= $this->modelTimer->getAll( $conditions );

		$this->addData( 'userId', $this->userId );
		$this->addData( 'timers', $timers );
		$this->addData( 'timer', $timer );
		$this->addData( 'limit', $limit );
		$this->addData( 'page', $page );
		$this->addData( 'total', $total );
		$this->addData( 'projectMap', $this->projectMap );
		$this->addData( 'from', $this->request->get( 'from' ) );
		$this->addData( 'unrelatedTimers', $unrelatedTimers );
	}

	public function pause( $timerId )
	{
		try{
			$this->logicTimer->pause( $timerId );
			if( $this->request->get( 'from' ) )
				$this->restart( $this->request->get( 'from' ) );
			$this->restart( NULL, TRUE );
		}
		catch( Exception $e ){
			$this->messenger->noteError( 'Fehler: '.$e->getMessage() );
			$this->restart( NULL, TRUE );
		}
	}

	public function remove( $timerId )
	{
		$this->restart( NULL, TRUE );
	}

	public function start( $timerId )
	{
		try{
			$this->logicTimer->start( $timerId );
			if( $this->request->get( 'from' ) )
				$this->restart( $this->request->get( 'from' ) );
			$this->restart( NULL, TRUE );
		}
		catch( Exception $e ){
			$this->messenger->noteError( 'Fehler: '.$e->getMessage() );
			$this->restart( NULL, TRUE );
		}
	}

	public function stop( $timerId )
	{
		try{
			$this->logicTimer->stop( $timerId );
			if( $this->request->get( 'from' ) )
				$this->restart( $this->request->get( 'from' ) );
			$this->restart( NULL, TRUE );
		}
		catch( Exception $e ){
			$this->messenger->noteError( 'Fehler: '.$e->getMessage() );
			$this->restart( NULL, TRUE );
		}

		if( $this->request->get( 'from' ) )
			$this->restart( $this->request->get( 'from' ) );
		$this->restart( NULL, TRUE );
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->messenger		= $this->env->getMessenger();
		$this->userId			= $this->session->get( 'auth_user_id' );
		$this->logicTimer		= Logic_Work_Timer::getInstance( $this->env );
		$this->logicProject		= Logic_Project::getInstance( $this->env );
//		$this->modelProject		= new Model_Project( $this->env );
		$this->modelTimer		= new Model_Work_Timer( $this->env );
		$this->projectMap		= $this->logicProject->getUserProjects( $this->userId, TRUE );
		$this->addData( 'filterProjectId', $this->session->get( 'filter_work_timer_projectId' ) );
		$this->addData( 'filterStatus', (int) $this->session->get( 'filter_work_timer_status' ) );
		$this->addData( 'userId', $this->userId );
	}
}
