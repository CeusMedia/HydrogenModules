<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Work_Time extends Controller
{
	protected HttpRequest $request;
	protected Dictionary $session;
	protected MessengerResource $messenger;
	protected ?string $userId;
	protected Logic_Work_Timer $logicTimer;
	protected Logic_Project $logicProject;
	protected Model_Work_Timer $modelTimer;
	protected array $projectMap;
	protected array $modules			= [];

	/**
	 * @return void
	 * @throws ReflectionException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
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

			$data		= [
				'projectId'			=> $projectId,
				'module'			=> $module,
				'moduleId'			=> $moduleId,
				'userId'			=> $this->userId,
				'workerId'			=> $this->request->get( 'workerId' ),
				'title'				=> $title,
				'description'		=> $desc,
				'secondsPlanned'	=> $secondsPlanned,
				'secondsNeeded'		=> $secondsNeeded,
				'status'			=> 0,
				'createdAt'			=> time(),
				'modifiedAt'		=> time(),
			];

			$timerId	= $this->modelTimer->add( $data );
			$this->messenger->noteSuccess( 'Timer saved.' );
			$status		= (int) $this->request->get( 'status' );
			if( $status === 1 )
				$this->logicTimer->start( $timerId );
			else if( !in_array( $status, [0, 1] ) )
				$this->modelTimer->edit( $timerId, ['status' => $status] );
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

	/**
	 * @return void
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function assign(): void
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
				$count		+= $this->modelTimer->edit( $timerId, [
					'module'	=> $module,
					'moduleId'	=> $moduleId,
				] );
			}
			$this->messenger->noteSuccess( '%d Timer(s) assigned.', $count );
		}
		if( $this->request->get( 'from' ) )
			$this->restart( $this->request->get( 'from' ) );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		string		$timerId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( string $timerId ): void
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

		/** @var Logic_Authentication $logicAuth */
		$logicAuth		= Logic_Authentication::getInstance( $this->env );
		$currentUserId	= $logicAuth->getCurrentUserId();
		$projectUsers	= [];
		if( $timer->projectId ){
			/** @var Logic_Project $logicProject */
			$logicProject   = Logic_Project::getInstance( $this->env );
			$projectUsers	= $logicProject->getProjectUsers( $timer->projectId, [], ['username' => 'ASC'] );
			if( !$timer->workerId )
				$timer->workerId	= $currentUserId;
		}
		$this->addData( 'projectUsers', $projectUsers );
	}

	public function filter(): void
	{
		$this->session->set( 'filter_work_timer_activityId', $this->request->get( 'activityId' ) );
		$this->session->set( 'filter_work_timer_projectId', $this->request->get( 'projectId' ) );
		$this->session->set( 'filter_work_timer_status', $this->request->get( 'status' ) );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		int		$limit
	 *	@param		int		$page
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function index( int $limit = 10, int $page = 0 ): void
	{
		if( !$this->projectMap && !$this->env->getRequest()->isAjax() )
			$this->restart( './manage/project/add?from=work/time' );

		$conditions	= ['status' => [2]];
		$total		= $this->modelTimer->count( $conditions );
		$timers		= $this->modelTimer->getAll( $conditions, ['modifiedAt' => 'ASC'], [$page * $limit, $limit] );
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

		$conditions	= [
			'moduleId'	=> 0,
			'userId'	=> $this->userId
		];
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

	/**
	 *	@param		string		$timerId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function pause( string $timerId ): void
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

	/**
	 *	@param		string		$timerId
	 *	@return		void
	 */
	public function remove( string $timerId ): void
	{
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		string		$timerId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function start( string $timerId ): void
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

	/**
	 *	@param		string		$timerId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function stop( string $timerId ): void
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

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->messenger		= $this->env->getMessenger();
		$this->userId			= $this->session->get( 'auth_user_id' );
		$this->logicTimer		= Logic_Work_Timer::getInstance( $this->env );
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicProject		= Logic_Project::getInstance( $this->env );
//		$this->modelProject		= new Model_Project( $this->env );
		$this->modelTimer		= new Model_Work_Timer( $this->env );
		$this->projectMap		= $this->logicProject->getUserProjects( $this->userId, TRUE );
		$this->addData( 'filterProjectId', $this->session->get( 'filter_work_timer_projectId' ) );
		$this->addData( 'filterStatus', (int) $this->session->get( 'filter_work_timer_status' ) );
		$this->addData( 'userId', $this->userId );
	}
}
