<?php
/**
 *	Controller.
 *	@version		$Id$
 */
/**
 *	Controller.
 *	@version		$Id$
 *	@todo			implement
 *	@todo			code documentation
 */
class Controller_Work_Mission extends CMF_Hydrogen_Controller{

	protected $userMap			= array();
	protected $useIssues		= FALSE;
	protected $useProjects		= FALSE;
	protected $hasFullAccess	= FALSE;
	protected $logic;

	protected function __onInit(){
		$this->model	= new Model_Mission( $this->env );
		$this->logic	= new Logic_Mission( $this->env );
		$this->acl		= $this->env->getAcl();

		$model			= new Model_User( $this->env );
		foreach( $model->getAll() as $user )
			$this->userMap[$user->userId]	= $user;

		$modules	= $this->env->getModules();

		$this->addData( 'useProjects', $this->useProjects = $modules->has( 'Manage_Projects' ) );
		$this->addData( 'useIssues', $this->useIssues = $modules->has( 'Manage_Issues' ) );
	}

	public function add(){
		$config			= $this->env->getConfig();
		$session		= $this->env->getSession();
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'add' );
		$userId			= $session->get( 'userId' );

		$content	= $request->get( 'content' );
		$status		= $request->get( 'status' );
		$dayStart	= !$request->get( 'type' ) ? $request->get( 'dayWork' ) : $request->get( 'dayStart' );
		$dayEnd		= !$request->get( 'type' ) ? $request->get( 'dayDue' ) : $request->get( 'dayEnd' );

		if( $request->get( 'add' ) ){
			if( !$content )
				$messenger->noteError( $words->msgNoContent );
			if( !$messenger->gotError() ){
				$data	= array(
					'ownerId'		=> (int) $userId,
					'workerId'		=> (int) $request->get( 'workerId' ),
					'projectId'		=> (int) $request->get( 'projectId' ),
					'type'			=> (int) $request->get( 'type' ),
					'priority'		=> (int) $request->get( 'priority' ),
					'status'		=> $status,
					'content'		=> $content,
					'dayStart'		=> $this->logic->getDate( $dayStart ),
					'dayEnd'		=> $this->logic->getDate( $dayEnd ),
					'timeStart'		=> $request->get( 'timeStart' ),
					'timeEnd'		=> $request->get( 'timeEnd' ),
					'location'		=> $request->get( 'location' ),
					'reference'		=> $request->get( 'reference' ),
					'createdAt'		=> time(),
				);
				$this->model->add( $data );
				$messenger->noteSuccess( $words->msgSuccess );
				$this->restart( './work/mission' );
			}
		}
		$mission	= array();
		foreach( $this->model->getColumns() as $key )
			$mission[$key]	= strlen( $request->get( $key ) ) ? $request->get( $key ) : NULL;
		if( $mission['priority'] === NULL )
			$mission['priority']	= 3;
		if( $mission['status'] === NULL )
			$mission['status']	= 0;
		$this->addData( 'mission', (object) $mission );
		$this->addData( 'users', $this->userMap );
		$this->addData( 'userId', $userId );
		$this->addData( 'day', (int) $session->get( 'filter_mission_day' ) );

		if( $this->useProjects )
			$this->addData( 'userProjects', $this->logic->getUserProjects( $session->get( 'userId' ), TRUE ) );
	}

	public function ajaxSelectDay( $day ){
		$this->env->getSession()->set( 'filter_mission_day', (int) $day );
		print( json_encode( (int) $day ) );
		exit;
	}

	public function changeDay( $missionId ){
		$date		= trim( $this->env->getRequest()->get( 'date' ) );
		$mission	= $this->model->get( $missionId );
		$data		= array();
		if( preg_match( "/^[+-][0-9]+$/", $date ) ){
			$sign	= substr( $date, 0, 1 );					//  extract direction to move
			$number	= substr( $date, 1 );						//  extract number of days to move
			$change	= $sign." ".$number."day";
			$date	= new  DateTime( $mission->dayStart );
			$data['dayStart'] = $date->modify( $change )->format( "Y-m-d" );
			if( $mission->dayEnd ){
				$date	= new  DateTime( $mission->dayEnd );
				$data['dayEnd'] = $date->modify( $change )->format( "Y-m-d" );
			}
			$this->model->edit( $missionId, $data );
		}
		$this->restart( NULL, TRUE );
	}

	public function edit( $missionId ){
		$config			= $this->env->getConfig();
		$session		= $this->env->getSession();
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'edit' );
		$userId			= $session->get( 'userId' );

		$mission	= $this->model->get( $missionId );
		if( !$mission )
			$messenger->noteError( $words->msgInvalidId );
		if( $this->useProjects ){
			if( $this->hasFullAccess() )
				$userProjects	= $this->logic->getUserProjects( $userId );
			$userProjects	= $this->logic->getUserProjects( $userId, TRUE );
			if( !array_key_exists( $mission->projectId, $userProjects ) )
				$messenger->noteError( $words->msgInvalidProject );
		}
		if( $messenger->gotError() )
			$this->restart( NULL, TRUE );

		$content	= $request->get( 'content' );
		$dayStart	= $request->get( 'dayStart' );
		$dayEnd		= $request->get( 'dayEnd' );
		if( $request->get( 'type' ) == 0 ){
			$dayStart	= $this->logic->getDate( $request->get( 'dayWork' ) );
			$dayEnd		= $request->get( 'dayDue' ) ? $this->logic->getDate( $request->get( 'dayDue' ) ) : NULL;
		}

		if( $request->get( 'edit' ) ){
			if( !$content )
				$messenger->noteError( $words->msgNoContent );
			if( !$messenger->gotError() ){
				$data	= array(
					'workerId'		=> (int) $request->get( 'workerId' ),
					'projectId'		=> (int) $request->get( 'projectId' ),
					'type'			=> (int) $request->get( 'type' ),
					'priority'		=> (int) $request->get( 'priority' ),
					'content'		=> $content,
					'status'		=> (int) $request->get( 'status' ),
					'dayStart'		=> $dayStart,
					'dayEnd'		=> $dayEnd,
					'timeStart'		=> $request->get( 'timeStart' ),
					'timeEnd'		=> $request->get( 'timeEnd' ),
					'location'		=> $request->get( 'location' ),
					'reference'		=> $request->get( 'reference' ),
					'modifiedAt'	=> time(),
				);
				$this->model->edit( $missionId, $data, FALSE );
				$messenger->noteSuccess( $words->msgSuccess );
				$this->restart( './work/mission' );
			}
		}
		$modelUser	= new Model_User( $this->env );
		$mission->owner		= array_key_exists( $mission->ownerId, $this->userMap ) ? $this->userMap[$mission->ownerId] : NULL;
		$mission->worker	= array_key_exists( $mission->workerId, $this->userMap ) ? $this->userMap[$mission->workerId] : NULL;
		$this->addData( 'mission', $mission );
		$this->addData( 'users', $this->userMap );
		$missionUsers		= array( $mission->ownerId => $mission->owner );
		if( $mission->workerId )
			$missionUsers[$mission->workerId]	= $mission->worker;

		if( $this->useProjects ){
			$model		= new Model_Project( $this->env );
			foreach( $model->getProjectUsers( (int) $mission->projectId ) as $user )
				$missionUsers[$user->userId]	= $user;

			$userId		= $session->get( 'userId' );
			if( $this->hasFullAccess() )
				$userProjects	= $this->logic->getUserProjects( $userId );
			$userProjects	= $this->logic->getUserProjects( $userId, TRUE );
			$this->addData( 'userProjects', $userProjects );
		}
		$this->addData( 'missionUsers', $missionUsers );

		if( $this->useIssues ){
			$this->env->getLanguage()->load( 'work/issue' );
			$this->addData( 'wordsIssue', $this->env->getLanguage()->getWords( 'work/issue' ) );
		}
	}

	protected function hasFullAccess(){
		return $this->env->getAcl()->hasFullAccess( $this->env->getSession()->get( 'roleId' ) );
	}

	public function export( $format = NULL, $debug = FALSE ){
		switch( $format ){
			case 'ical':
				$ical	= $this->exportAsIcal( $debug );
				$debug ? xmp( $ical ) : print( $ical );
				die;
				break;
			default:
				$missions	= $this->model->getAll();												//  get all missions
				$zip		= gzencode( serialize( $missions ) );									//  gzip serial of mission objects
				Net_HTTP_Download::sendString( $zip , 'missions_'.date( 'Ymd' ).'.gz' );			//  deliver downloadable file
		}
	}

	protected function exportAsIcal(){
		$userId	= $this->env->getSession()->get( 'userId' );
		if( !$userId ){
			$auth	= new BasicAuthentication( $this->env, 'Export' );
			$userId	= $auth->authenticate();
		}
		$conditions	= array( 'status' => array( 0, 1, 2, 3 ) );
		$orders		= array( 'dayStart' => 'ASC' );
		$missions	= $this->logic->getUserMissions( $userId, $conditions, $orders );

		$root		= new XML_DOM_Node( 'event');
		$calendar	= new XML_DOM_Node( 'VCALENDAR' );
		$calendar->addChild( new XML_DOM_Node( 'VERSION', '2.0' ) );
		foreach( $missions as $mission ){
			switch( $mission->type ){
				case 0:
					$date	= date( "Ymd", strtotime( $mission->dayStart ) + 24 * 60 * 60 -1 );
					$node	= new XML_DOM_Node( 'VTODO' );
					$node->addChild( new XML_DOM_Node( 'DUE', $date, array( 'VALUE' => 'DATE' ) ) );
#					$node->addChild( new XML_DOM_Node( 'STATUS', 'NEEDS-ACTION' ) );
					break;
				case 1:
					$node	= new XML_DOM_Node( 'VEVENT' );
					if( $mission->dayStart ){
						$day	= $mission->dayStart;
						if( strlen( $mission->timeStart ) )
							$day	.= ' '.$mission->timeStart;
						$datetime	= date( "Ymd\THis", strtotime( $day ) );
						$node->addChild( new XML_DOM_Node( 'DTSTART', $datetime ) );
					}
					if( !$mission->dayEnd && $mission->dayStart )
						$mission->dayEnd	= $mission->dayStart;
					if( $mission->dayEnd ){
						$day	= $mission->dayEnd;
						if( strlen( $mission->timeEnd ) )
							$day	.= ' '.$mission->timeEnd;
						else if( $mission->timeStart && $mission->dayStart == $mission->dayEnd ){
							$parts	= explode( ':', $mission->timeStart );
							$day	.= ' '.str_pad( ++$parts[0], 2, 0, STR_PAD_LEFT ).':'.$parts[1];
						}
						$datetime	= date( "Ymd\THis", strtotime( $day ) );
						$node->addChild( new XML_DOM_Node( 'DTEND', $datetime ) );
					}
					break;
			}
			$node->addChild( new XML_DOM_Node( 'SUMMARY', $mission->content ) );
			$node->addChild( new XML_DOM_Node( 'CREATED', date( "Ymd\THis", $mission->createdAt ) ) );
			if( $mission->modifiedAt )
				$node->addChild( new XML_DOM_Node( 'LAST-MODIFIED', date( "Ymd\THis", $mission->modifiedAt ) ) );
			if( $mission->location )
				$node->addChild( new XML_DOM_Node( 'LOCATION', $mission->location ) );
			if( $mission->priority )
				$node->addChild( new XML_DOM_Node( 'PRIORITY', ( ceil( $mission->priority - 7 ) / -2 ) ) );
			$calendar->addChild( $node );
		}
		$root->addChild( $calendar );
		$ical	= new File_ICal_Builder();
		$ical	= trim( $ical->build( $root ) );
		error_log( date( 'Y-m-d H:i:s' ).' | '.getEnv( 'REMOTE_ADDR' ).': '.getEnv( 'HTTP_USER_AGENT' )."\n", 3, 'ua.log' );
#		if( 1 )
#			header( 'Content-type: text/plain;charset=utf-8' );
#		if( 1 )
#			header( 'Content-type: text/calendar' );
#		else if( 1 )
#		Net_HTTP_Download::sendString( $ical , 'ical_'.date( 'Ymd' ).'.ics' );			//  deliver downloadable file
		return $ical;
	}

	public function filter(){
		$request		= $this->env->getRequest();
		$session		= $this->env->getSession();
		if( $request->has( 'reset' ) ){
			$session->remove( 'filter_mission_access' );
			$session->remove( 'filter_mission_query' );
			$session->remove( 'filter_mission_types' );
			$session->remove( 'filter_mission_priorities' );
			$session->remove( 'filter_mission_states' );
			$session->remove( 'filter_mission_projects' );
			$session->remove( 'filter_mission_order' );
			$session->remove( 'filter_mission_direction' );
			$session->remove( 'filter_mission_day' );
		}
		if( $request->has( 'access' ) )
			$session->set( 'filter_mission_access', $request->get( 'access' ) );
		if( $request->has( 'query' ) )
			$session->set( 'filter_mission_query', $request->get( 'query' ) );
		if( $request->has( 'type' ) )
			$session->set( 'filter_mission_types', $request->get( 'types' ) );
		if( $request->has( 'priority' ) )
			$session->set( 'filter_mission_priorities', $request->get( 'priorities' ) );
		if( $request->has( 'status' ) )
			$session->set( 'filter_mission_states', $request->get( 'states' ) );
		if( $request->has( 'projects' ) )
			$session->set( 'filter_mission_projects', $request->get( 'projects' ) );
		if( $request->has( 'order' ) )
			$session->set( 'filter_mission_order', $request->get( 'order' ) );
		if( $request->has( 'direction' ) )
			$session->set( 'filter_mission_direction', $request->get( 'direction' ) );
#			if( $request->has( 'direction' ) )
#				$session->set( 'filter_mission_direction', $request->get( 'direction' ) );
		$this->restart( '', TRUE );
	}

	public function import(){
		$messenger		= $this->env->getMessenger();
		$file	= $this->env->getRequest()->get( 'serial' );
		if( $file['error'] != 0 ){
			$handler	= new Net_HTTP_UploadErrorHandler();
			$messenger->noteError( 'Upload-Fehler: '.$handler->getErrorMessage( $file['error'] ) );
		}
		else{
			$gz			= File_Reader::load( $file['tmp_name'] );
			$serial		= @gzinflate( substr( $gz, 10, -8 ) );
			$missions	= @unserialize( $serial );
			if( !$serial )
				$messenger->noteError( 'Das Entpacken der Daten ist fehlgeschlagen.' );
			else if( !$missions )
				$messenger->noteError( 'Keine Daten enthalten.' );
			else{
				$model	= new Model_Mission( $this->env );
				$model->truncate();
				foreach( $missions as $mission )
					$model->add( (array) $mission );
				$messenger->noteSuccess( 'Die Daten wurden importiert.' );
			}
		}
		$this->restart( NULL, TRUE );
	}

	public function calendar( $year = NULL, $month = NULL ){
		$session		= $this->env->getSession();

		if( $year === NULL || $month === NULL ){
			$year	= date( "Y" );
			if( $session->has( 'work-mission-view-year' ) )
				$year	= $session->get( 'work-mission-view-year' );
			$month	= date( "m" );
			if( $session->has( 'work-mission-view-month' ) )
				$month	= $session->get( 'work-mission-view-month' );
			$this->restart( './work/mission/calendar/'.$year.'/'.$month );
		}
		if( $month < 1 || $month > 12 ){
			while( $month > 12 ){
				$month	-= 12;
				$year	++;
			}
			while( $month < 1 ){
				$month	+= 12;
				$year	--;
			}
			$this->restart( './work/mission/calendar/'.$year.'/'.$month );
		}
		$session->set( 'work-mission-view-year', $year );
		$session->set( 'work-mission-view-month', $month );

		$this->setData( array(
			'userId'	=> $session->get( 'userId' ),
			'year'		=> $year,
			'month'		=> $month,
		) );
	}

	/**
	 *	Default action on this controller.
	 *	@access		public
	 *	@return		void
	 */
	public function index( $missionId = NULL ){
		if( trim( $missionId ) )
			$this->restart( 'edit/'.$missionId, TRUE );

		$config			= $this->env->getConfig();
		$session		= $this->env->getSession();
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'index' );

		if( $request->has( 'view' ) )
			$session->set( 'work-mission-view-type', (int) $request->get( 'view' ) );

		if( (int) $session->get( 'work-mission-view-type' ) == 1 )
			$this->restart( './work/mission/calendar' );

		$userId			= $session->get( 'userId' );
		$this->addData( 'userId', $userId );
		$this->addData( 'viewType', (int) $session->get( 'work-mission-view-type' ) );

		$access		= $session->get( 'filter_mission_access' );
		$query		= $session->get( 'filter_mission_query' );
		$types		= $session->get( 'filter_mission_types' );
		$priorities	= $session->get( 'filter_mission_priorities' );
		$states		= $session->get( 'filter_mission_states' );
		$projects	= $session->get( 'filter_mission_projects' );
		$direction	= $session->get( 'filter_mission_direction' );
		$order		= $session->get( 'filter_mission_order' );
		if( !$order )
			$this->restart( './work/mission/filter?order=priority' );
		if( !$access )
			$this->restart( './work/mission/filter?access=worker' );

		$direction	= $direction ? $direction : 'ASC';
		$session->set( 'filter_mission_direction', $direction );
		$orders		= array(					//  collect order pairs
			$order		=> $direction,				//  selected or default order and direction
			'timeStart'	=> 'ASC',				//  order events by start time
			'content'	=> 'ASC',				//  order by title at last
		);

		$conditions	= array();
		if( is_array( $types ) && count( $types ) )
			$conditions['type']	= $types;
		if( is_array( $priorities ) && count( $priorities ) )
			$conditions['priority']	= $priorities;
		if( !( is_array( $states ) && count( $states ) ) )
			$states	= array( 0, 1, 2, 3 );
		$conditions['status']	= $states;
		if( strlen( $query ) )
			$conditions['content']	= '%'.str_replace( array( '*', '?' ), '%', $query ).'%';
		if( is_array( $projects ) && count( $projects ) )											//  if filtered by projects
			$conditions['projectId']	= $projects;												//  apply project conditions

		$this->setData( array(																		//  assign data to view
			'missions'		=> $this->logic->getUserMissions( $userId, $conditions, $orders ),		//  add user missions
			'userProjects'	=> $this->logic->getUserProjects( $userId, TRUE ),						//  add user projects
			'users'			=> $this->userMap,														//  add user map
			'currentDay'	=> (int) $session->get( 'filter_mission_day' ),							//  set currently selected day
		) );

		$this->addData( 'filterTypes', $session->get( 'filter_mission_types' ) );
		$this->addData( 'filterPriorities', $session->get( 'filter_mission_priorities' ) );
		$this->addData( 'filterStates', $session->get( 'filter_mission_states' ) );
		$this->addData( 'filterOrder', $session->get( 'filter_mission_order' ) );
		$this->addData( 'filterProjects', $session->get( 'filter_mission_projects' ) );
		$this->addData( 'filterDirection', $direction );
	}

	public function setPriority( $missionId, $priority, $showMission = FALSE ){
		$this->model->edit( $missionId, array( 'priority' => $priority ) );							//  store new priority
		if( !$showMission )																			//  back to list
			$this->restart( NULL, TRUE );															//  jump to list
		$this->restart( 'edit/'.$missionId, TRUE );													//  otherwise jump to or stay in mission
	}

	public function setStatus( $missionId, $status, $showMission = FALSE ){
		$this->model->edit( $missionId, array( 'status' => $status ) );								//  store new status
		if( $status < 0 || !$showMission )															//  mission aborted/done or back to list
			$this->restart( NULL, TRUE );															//  jump to list
		$this->restart( 'edit/'.$missionId, TRUE );													//  otherwise jump to or stay in mission
	}
}
?>
