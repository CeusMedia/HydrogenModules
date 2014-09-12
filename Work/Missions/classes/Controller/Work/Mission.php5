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

	protected $acl;
	protected $filterKeyPrefix	= 'filter.work.mission.';
	protected $isEditor;
	protected $isViewer;
	protected $hasFullAccess	= FALSE;
	protected $logic;
	protected $messenger;
	protected $request;
	protected $session;
	protected $useIssues		= FALSE;
	protected $useProjects		= FALSE;
	protected $userMap			= array();

	protected $defaultFilterValues	= array(
		'mode'		=> 'now',
		'states'	=> array(
			Model_Mission::STATUS_NEW,
			Model_Mission::STATUS_ACCEPTED,
			Model_Mission::STATUS_PROGRESS,
			Model_Mission::STATUS_READY
		),
		'priorities'	=> array(
			Model_Mission::PRIORITY_NONE,
			Model_Mission::PRIORITY_HIGHEST,
			Model_Mission::PRIORITY_HIGH,
			Model_Mission::PRIORITY_NORMAL,
			Model_Mission::PRIORITY_LOW,
			Model_Mission::PRIORITY_LOWEST
		),
		'types'			=> array(
			Model_Mission::TYPE_TASK,
			Model_Mission::TYPE_EVENT
		),
		'order'			=> 'priority',
		'direction'		=> 'ASC',
	);

	protected function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->acl			= $this->env->getAcl();

		$this->model		= new Model_Mission( $this->env );
		$this->logic		= new Logic_Mission( $this->env );

		$this->isEditor		= $this->acl->has( 'work/mission', 'edit' );
		$this->isViewer		= $this->acl->has( 'work/mission', 'view' );
		$this->useProjects	= $this->env->getModules()->has( 'Manage_Projects' );
		$this->useIssues	= $this->env->getModules()->has( 'Manage_Issues' );

		$userId				= $this->session->get( 'userId' );

		if( !$userId || !$this->isViewer )
			$this->restart( NULL, FALSE, 401 );

		//  @todo	kriss: DO NOT DO THIS!!! (badly scaling)
		$model			= new Model_User( $this->env );
		foreach( $model->getAll() as $user )
			$this->userMap[$user->userId]	= $user;

		$this->addData( 'useProjects', $this->useProjects );
		$this->addData( 'useIssues', $this->useIssues );

		$this->userProjects		= $this->logic->getUserProjects( $userId, TRUE );
		if( $this->hasFullAccess() )
			$this->userProjects		= $this->logic->getUserProjects( $userId );

		$this->initFilters( $userId );
	}

	protected function initFilters( $userId ){
		if( !(int) $userId )
			return;
		if( !$this->session->getAll( 'filter.work.mission.', TRUE )->count() )
			$this->recoverFilters( $userId );

		//  --  DEFAULT SETTINGS  --  //
		$this->initDefaultFilters();

		//  --  GENERAL LOGIC CONDITIONS  --  //
		$mode	= $this->session->get( 'filter.work.mission.mode' );
		$this->logic->generalConditions['status']		= $this->defaultFilterValues['states'];
		switch( $mode ){
			case 'now':
				$this->logic->generalConditions['dayStart']	= '<'.date( "Y-m-d", time() + 7 * 24 * 60 * 60 );				//  @todo: kriss: calculation is incorrect
				break;
//			case 'future':
//				$this->logic->generalConditions['dayStart']	= '>='.date( "Y-m-d", time() + 6 * 24 * 60 * 60 );				//  @todo: kriss: calculation is incorrect
//				break;
		}
	}

	public function add(){
		$config			= $this->env->getConfig();
		$words			= (object) $this->getWords( 'add' );
		$userId			= $this->session->get( 'userId' );

		if( !$this->isEditor ){
			$this->messenger->noteError( $words->msgNotEditor );
			$this->restart( NULL, TRUE, 403 );
		}

		$title		= $this->request->get( 'title' );
		$status		= $this->request->get( 'status' );
		$dayStart	= !$this->request->get( 'type' ) ? $this->request->get( 'dayWork' ) : $this->request->get( 'dayStart' );
		$dayEnd		= !$this->request->get( 'type' ) ? $this->request->get( 'dayDue' ) : $this->request->get( 'dayEnd' );

		if( $this->request->has( 'add' ) ){
			if( !$title )
				$this->messenger->noteError( $words->msgNoTitle );
			if( !$this->messenger->gotError() ){
				$data	= array(
					'ownerId'			=> (int) $userId,
					'workerId'			=> (int) $this->request->get( 'workerId' ),
					'projectId'			=> (int) $this->request->get( 'projectId' ),
					'type'				=> (int) $this->request->get( 'type' ),
					'priority'			=> (int) $this->request->get( 'priority' ),
					'status'			=> $status,
					'title'				=> $title,
					'content'			=> $this->request->get( 'content' ),
					'dayStart'			=> $this->logic->getDate( $dayStart ),
					'dayEnd'			=> $this->logic->getDate( $dayEnd ),
					'timeStart'			=> $this->request->get( 'timeStart' ),
					'timeEnd'			=> $this->request->get( 'timeEnd' ),
					'minutesProjected'	=> $this->getMinutesFromInput( $this->request->get( 'minutesProjected' ) ),
					'location'			=> $this->request->get( 'location' ),
					'reference'			=> $this->request->get( 'reference' ),
					'createdAt'			=> time(),
				);
				$missionId	= $this->model->add( $data );
				$this->messenger->noteSuccess( $words->msgSuccess );
				$this->logic->noteChange( 'new', $missionId, NULL, $userId );
				$this->restart( './work/mission' );
			}
		}
		$mission	= array();
		foreach( $this->model->getColumns() as $key )
			$mission[$key]	= strlen( $this->request->get( $key ) ) ? $this->request->get( $key ) : NULL;
		if( $mission['priority'] === NULL )
			$mission['priority']	= 3;
		if( $mission['status'] === NULL )
			$mission['status']	= 0;
		$mission['minutesProjected']	= $this->getMinutesFromInput( $this->request->get( 'minutesProjected' ) );
		$this->addData( 'mission', (object) $mission );
		$this->addData( 'users', $this->userMap );
		$this->addData( 'userId', $userId );
		$this->addData( 'day', (int) $this->session->get( $this->filterKeyPrefix.'day' ) );

		if( $this->useProjects )
			$this->addData( 'userProjects', $this->userProjects );
	}

	public function ajaxRenderContent(){
		$mode	= $this->session->get( 'filter.work.mission.mode' );
		if( $mode && $mode !== 'now' )
			$this->redirect( 'work/mission/'.$mode, 'ajaxRenderContent', func_get_args() );
		else{
			$words		= $this->getWords();

			$day		= (int) $this->session->get( 'filter.work.mission.day' );

			$userId		= $this->session->get( 'userId' );
			$missions	= $this->getFilteredMissions( $userId );

			$missions	= array_slice( $missions, 0, 100 );										//  @todo	kriss: make configurable

			$listLarge		= new View_Helper_Work_Mission_List_Days( $this->env );
			$listLarge->setMissions( $missions );
			$listLarge->setWords( $words );

			$listSmall		= new View_Helper_Work_Mission_List_DaysSmall( $this->env );
			$listSmall->setMissions( $listLarge->getDayMissions( $day ) );
			$listSmall->setWords( $words );

			$buttonsLarge	= new View_Helper_Work_Mission_List_DayControls( $this->env );
			$buttonsLarge->setWords( $words );
			$buttonsLarge->setDayMissions( $listLarge->getDayMissions() );

			$buttonsSmall	= new View_Helper_Work_Mission_List_DayControlsSmall( $this->env );
			$buttonsSmall->setWords( $words );
			$buttonsSmall->setDayMissions( $listLarge->getDayMissions() );

			$data		= array(
				'day'		=> $day,
				'items'		=> $listLarge->getDayMissions( $day ),
				'count'		=> count( $listLarge->getDayMissions( $day ) ),
				'buttons'	=> array(
					'large'	=> $buttonsLarge->render(),
					'small'	=> $buttonsSmall->render(),
				),
				'lists'		=> array(
					'large'	=> $listLarge->renderDayList( 1, $day, TRUE, TRUE, FALSE, TRUE ),
					'small'	=> $listSmall->renderDayList( 1, $day, TRUE, TRUE, FALSE, TRUE )
				)
			);
			print( json_encode( $data ) );
			exit;
		}
	}

	public function ajaxSaveContent( $missionId ){
		$data		= array(
			'content'	=> $this->env->getRequest()->get( 'content' ),
		);
		$this->model->edit( $missionId, $data, FALSE );
		exit;
	}

	public function ajaxSelectDay( $day ){
		$this->env->getSession()->set( $this->filterKeyPrefix.'day', (int) $day );
		$this->ajaxRenderContent();
	}

	protected function assignFilters(){
		$userId		= $this->session->get( 'userId' );
		$this->addData( 'userId', $userId );
		$this->addData( 'viewType', (int) $this->session->get( 'work-mission-view-type' ) );

		$direction	= $this->session->get( $this->filterKeyPrefix.'direction' );
		$order		= $this->session->get( $this->filterKeyPrefix.'order' );

		if( !$order )
			$this->restart( './work/mission/filter?order=priority' );

		$direction	= $direction ? $direction : 'ASC';
		$this->session->set( $this->filterKeyPrefix.'direction', $direction );

		$this->setData( array(																		//  assign data t$
			'userProjects'	=> $this->userProjects,													//  add user projec$
			'users'			=> $this->userMap,														//  add user map
		) );

		$this->addData( 'filterTypes', $this->session->get( $this->filterKeyPrefix.'types' ) );
		$this->addData( 'filterPriorities', $this->session->get( $this->filterKeyPrefix.'priorities' ) );
		$this->addData( 'filterStates', $this->session->get( $this->filterKeyPrefix.'states' ) );
		$this->addData( 'filterOrder', $order );
		$this->addData( 'filterProjects', $this->session->get( $this->filterKeyPrefix.'projects' ) );
		$this->addData( 'filterDirection', $direction );
		$this->addData( 'filterMode', $this->session->get( 'filter.work.mission.mode' ) );
		$this->addData( 'filterQuery', $this->session->get( $this->filterKeyPrefix.'query' ) );
		$this->addData( 'defaultFilterValues', $this->defaultFilterValues );
		$this->addData( 'wordsFilter', $this->env->getLanguage()->getWords( 'work/mission' ) );
	}

	public function calendar( $year = NULL, $month = NULL ){
		if( $year === NULL || $month === NULL ){
			$year	= date( "Y" );
			if( $this->session->has( 'work-mission-view-year' ) )
				$year	= $this->session->get( 'work-mission-view-year' );
			$month	= date( "m" );
			if( $this->session->has( 'work-mission-view-month' ) )
				$month	= $this->session->get( 'work-mission-view-month' );
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
		$this->session->set( 'work-mission-view-year', $year );
		$this->session->set( 'work-mission-view-month', $month );

		$this->setData( array(
			'userId'	=> $this->session->get( 'userId' ),
			'year'		=> $year,
			'month'		=> $month,
		) );
	}

	/**
 	 *	Moves a mission by several days or to a given date.
	 *	Receives date or day difference using POST.
	 *	A day difference can be formatted like +2 or -2.
	 *	Moving a task mission will only affect start date but end date will remain unchanged.
	 *	Moving an event mission will affect start and end date.
	 *	If called using AJAX list rendering is triggered.
	 *	@access		public
	 *	@param		integer		$mission		ID of mission to move in time
	 *	@return		void
	 *	@todo		kriss: enable this feature for AJAX called EXCEPT gid list
	 */
	public function changeDay( $missionId ){
		$userId		= $this->env->getSession()->get( 'userId' );
		$date		= trim( $this->env->getRequest()->get( 'date' ) );
		$mission	= $this->model->get( $missionId );
		$data		= array();
		$change		= "";

		if( preg_match( "/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]+$/", $date ) ){
			$date	= strtotime( $date );
			$diff	= ( $date - strtotime( $mission->dayStart ) ) / ( 24 * 3600 );
			$sign	= $diff >= 0 ? "+" : "-";
			$number	= abs( $diff );
			$change	= $sign." ".$number."day";
		}
		else if( preg_match( "/^[+-][0-9]+$/", $date ) ){
			$sign	= substr( $date, 0, 1 );					//  extract direction to move
			$number	= substr( $date, 1 );						//  extract number of days to move
			$change	= $sign." ".$number."day";
		}
		if( $change ){
			$date	= new  DateTime( $mission->dayStart );
			$data['dayStart'] = $date->modify( $change )->format( "Y-m-d" );
			if( $mission->dayEnd ){													//  mission has a duration
				if( $mission->type == 1 ){											//  mission is an event, not a task
					$date	= new  DateTime( $mission->dayEnd );					//  take end timestamp and ... 
					$data['dayEnd'] = $date->modify( $change )->format( "Y-m-d" );  //  ... store new moved end date
				}
			}
			$this->model->edit( $missionId, $data );
			$this->logic->noteChange( 'update', $missionId, $mission, $userId );
		}
		if( $this->env->request->isAjax() )
			$this->ajaxRenderList();
		$this->restart( NULL, TRUE );
	}

	protected function checkIsEditor( $missionId = NULL, $strict = TRUE, $status = 403 ){
		if( $this->isEditor )
			return TRUE;
		if( !$strict )
			return FALSE;
		$words		= (object) $this->getWords( 'msg' );
		$message	= $words->errorNoRightToAdd;
		$redirect	= NULL;
		if( $missionId ){
			$message	= $words->errorNoRightToEdit;
			$redirect	= 'view/'.$missionId;
		}
		$this->env->getMessenger()->noteError( $message );
		$this->restart( $redirect, TRUE, $status );
	}


	public function close( $missionId ){
		$this->checkIsEditor( $missionId );
		$userId			= $this->env->getSession()->get( 'userId' );
		$words			= (object) $this->getWords( 'edit' );
		$data			= array(
			'status'		=> $this->request->get( 'status' ),
			'hoursRequired'	=> $this->request->get( 'hoursRequired' ),
		);
		$mission		= $this->model->get( $missionId );
		$this->model->edit( $missionId, $data );
		$this->logic->noteChange( 'update', $missionId, $mission, $userId );
		$this->messenger->noteSuccess( $words->msgSuccessClosed );
		$this->restart( NULL, TRUE );
	}

	public function edit( $missionId ){
		$this->checkIsEditor( $missionId );
		$config			= $this->env->getConfig();
		$words			= (object) $this->getWords( 'edit' );
		$userId			= $this->session->get( 'userId' );
		$mission		= $this->model->get( $missionId );
		if( !$mission )
			$this->messenger->noteError( $words->msgInvalidId );
		if( $this->useProjects ){
			if( !array_key_exists( $mission->projectId, $this->userProjects ) )
				$this->messenger->noteError( $words->msgInvalidProject );
		}
		if( $this->messenger->gotError() )
			$this->restart( NULL, TRUE );

		$title		= $this->request->get( 'title' );
		$dayStart	= $this->request->get( 'dayStart' );
		$dayEnd		= $this->request->get( 'dayEnd' );
		if( $this->request->get( 'type' ) == 0 ){
			$dayStart	= $this->logic->getDate( $this->request->get( 'dayWork' ) );
			$dayEnd		= $this->request->get( 'dayDue' ) ? $this->logic->getDate( $this->request->get( 'dayDue' ) ) : NULL;
		}

		if( $this->request->get( 'edit' ) ){
			if( !$title )
				$this->messenger->noteError( $words->msgNoTitle );
			if( !$this->messenger->gotError() ){
				$data	= array(
					'workerId'			=> (int) $this->request->get( 'workerId' ),
					'projectId'			=> (int) $this->request->get( 'projectId' ),
					'type'				=> (int) $this->request->get( 'type' ),
					'priority'			=> (int) $this->request->get( 'priority' ),
					'title'				=> $title,
//					'content'			=> $this->request->get( 'content' ),
					'status'			=> (int) $this->request->get( 'status' ),
					'dayStart'			=> $dayStart,
					'dayEnd'			=> $dayEnd,
					'timeStart'			=> $this->request->get( 'timeStart' ),
					'timeEnd'			=> $this->request->get( 'timeEnd' ),
					'minutesProjected'	=> $this->getMinutesFromInput( $this->request->get( 'minutesProjected' ) ),
					'minutesRequired'	=> $this->getMinutesFromInput( $this->request->get( 'minutesRequired' ) ),
//					'hoursProjected'	=> $this->request->get( 'hoursProjected' ) ? $this->request->get( 'hoursProjected' ) : NULL,
//					'hoursRequired'		=> $this->request->get( 'hoursRequired' ) ? $this->request->get( 'hoursRequired' ) : NULL,
					'location'			=> $this->request->get( 'location' ),
					'reference'			=> $this->request->get( 'reference' ),
					'modifiedAt'		=> time(),
				);
print_m( $data );
				$this->model->edit( $missionId, $data, FALSE );
				$this->messenger->noteSuccess( $words->msgSuccess );
				$this->logic->noteChange( 'update', $missionId, $mission, $userId );
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
			$this->addData( 'userProjects', $this->userProjects );
		}
		$this->addData( 'missionUsers', $missionUsers );

		if( $this->useIssues ){
			$this->env->getLanguage()->load( 'work/issue' );
			$this->addData( 'wordsIssue', $this->env->getLanguage()->getWords( 'work/issue' ) );
		}
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
			$node->addChild( new XML_DOM_Node( 'SUMMARY', $mission->title ) );
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

	protected function getMinutesFromInput( $input ){
		if( !strlen( trim( $input ) ) )
			return 0;
		if( substr_count( $input, ":" ) ){
			$parts	= explode( ":", $input );
			return $parts[1] + $parts[0] * 60;
		}
		return (int) $input;
	}

	/**
	 * @todo	remove this because all methods receiver userId and this is using roleId from session
	 */
	protected function hasFullAccess(){
		return $this->env->getAcl()->hasFullAccess( $this->env->getSession()->get( 'roleId' ) );
	}

	protected function getModeFilterKeyPrefix(){
		$mode	= '';
		if( $this->session->get( 'filter.work.mission.mode' ) !== 'now' )
			$mode	= $this->session->get( 'filter.work.mission.mode' ).'.';
		return $this->filterKeyPrefix.$mode;
	}

	public function filter(){
		$sessionPrefix	= $this->getModeFilterKeyPrefix();
		if( $this->request->has( 'reset' ) ){
			$this->session->remove( $sessionPrefix.'query' );
			$this->session->remove( $sessionPrefix.'types' );
			$this->session->remove( $sessionPrefix.'priorities' );
			$this->session->remove( $sessionPrefix.'states' );
			$this->session->remove( $sessionPrefix.'projects' );
			$this->session->remove( $sessionPrefix.'order' );
			$this->session->remove( $sessionPrefix.'direction' );
			$this->session->remove( $sessionPrefix.'day' );
		}
		if( $this->request->has( 'access' ) )
			$this->session->set( $sessionPrefix.'access', $this->request->get( 'access' ) );
		if( $this->request->has( 'query' ) )
			$this->session->set( $sessionPrefix.'query', $this->request->get( 'query' ) );
		if( $this->request->has( 'types' ) )
			$this->session->set( $sessionPrefix.'types', $this->request->get( 'types' ) );
		if( $this->request->has( 'priorities' ) )
			$this->session->set( $sessionPrefix.'priorities', $this->request->get( 'priorities' ) );
		if( $this->request->has( 'states' ) )
			$this->session->set( $sessionPrefix.'states', $this->request->get( 'states' ) );
		if( $this->request->has( 'projects' ) )
			$this->session->set( $sessionPrefix.'projects', $this->request->get( 'projects' ) );
		if( $this->request->has( 'order' ) )
			$this->session->set( $sessionPrefix.'order', $this->request->get( 'order' ) );
		if( $this->request->has( 'direction' ) )
			$this->session->set( $sessionPrefix.'direction', $this->request->get( 'direction' ) );
#			if( $this->request->has( 'direction' ) )
#				$this->session->set( $sessionPrefix.'direction', $this->request->get( 'direction' ) );
		if( $this->request->isAjax() ){
			print( json_encode( (object) array(
				'session'	=> $this->session->getAll(),
				'request'	=> $this->request->getAll()
			) ) );
			exit;
		}
		$this->restart( '', TRUE );
//		$this->request->isAjax() ? exit : $this->restart( '', TRUE );
	}

	protected function getFilteredMissions( $userId, $additionalConditions = array(), $limit = 0, $offset = 0 ){
//		$config			= $this->env->getConfig();
//		$userId			= $this->session->get( 'userId' );

		$query		= $this->session->get( $this->filterKeyPrefix.'query' );
		$types		= $this->session->get( $this->filterKeyPrefix.'types' );
		$priorities	= $this->session->get( $this->filterKeyPrefix.'priorities' );
		$states		= $this->session->get( $this->filterKeyPrefix.'states' );
		$projects	= $this->session->get( $this->filterKeyPrefix.'projects' );
		$direction	= $this->session->get( $this->filterKeyPrefix.'direction' );
		$order		= $this->session->get( $this->filterKeyPrefix.'order' );
		$orders		= array(					//  collect order pairs
			$order		=> $direction,			//  selected or default order and direction
			'timeStart'	=> 'ASC',				//  order events by start time
		);
		if( $order != "title" )					//  if not ordered by title
			$orders['title']	= 'ASC';		//  order by title at last

		$conditions	= array();
		if( is_array( $types ) && count( $types ) )
			$conditions['type']	= $types;
		if( is_array( $priorities ) && count( $priorities ) )
			$conditions['priority']	= $priorities;
		if( is_array( $states ) && count( $states ) )
			$conditions['status']	= $states;
		if( strlen( $query ) )
			$conditions['title']	= '%'.str_replace( array( '*', '?' ), '%', $query ).'%';
		if( is_array( $projects ) && count( $projects ) )											//  if filtered by projects
			$conditions['projectId']	= $projects;												//  apply project conditions
		foreach( $additionalConditions as $key => $value )
			$conditions[$key]			= $value;
//print_m( $conditions );
//die;
		$limits	= array();
		if( $limit !== NULL && (int) $limit >= 10 ){
			$limits	= array( abs( $offset ), $limit );
		}
		return $this->logic->getUserMissions( $userId, $conditions, $orders, $limits );
	}

	public function import(){
		$this->checkIsEditor();
		$file	= $this->env->getRequest()->get( 'serial' );
		if( $file['error'] != 0 ){
			$handler	= new Net_HTTP_UploadErrorHandler();
			$this->messenger->noteError( 'Upload-Fehler: '.$handler->getErrorMessage( $file['error'] ) );
		}
		else{
			$gz			= File_Reader::load( $file['tmp_name'] );
			$serial		= @gzinflate( substr( $gz, 10, -8 ) );
			$missions	= @unserialize( $serial );
			if( !$serial )
				$this->messenger->noteError( 'Das Entpacken der Daten ist fehlgeschlagen.' );
			else if( !$missions )
				$this->messenger->noteError( 'Keine Daten enthalten.' );
			else{
				$model	= new Model_Mission( $this->env );
				$model->truncate();
				foreach( $missions as $mission )
					$model->add( (array) $mission );
				$this->messenger->noteSuccess( 'Die Daten wurden importiert.' );
			}
		}
		$this->restart( NULL, TRUE );
	}

	public function now(){
		$this->session->set( 'filter.work.mission.mode', 'now' );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	Default action on this controller.
	 *	@access		public
	 *	@return		void
	 */
	public function index( $missionId = NULL ){
		if( trim( $missionId ) )
			$this->restart( 'view/'.$missionId, TRUE );

		$mode	= $this->session->get( 'filter.work.mission.mode' );
		if( $mode === 'archive' )
			$this->restart( './work/mission/archive' );
		if( $mode === 'future' )
			$this->restart( './work/mission/future' );

		$config			= $this->env->getConfig();
		$words			= (object) $this->getWords( 'index' );

		if( $this->request->has( 'view' ) )
			$this->session->set( 'work-mission-view-type', (int) $this->request->get( 'view' ) );

		if( (int) $this->session->get( 'work-mission-view-type' ) == 1 )
			$this->restart( './work/mission/calendar' );

		$userId			= $this->session->get( 'userId' );
		$this->addData( 'userId', $userId );
		$this->addData( 'viewType', (int) $this->session->get( 'work-mission-view-type' ) );

		$this->assignFilters();

		$this->setData( array(																		//  assign data to view
			'missions'		=> $this->getFilteredMissions( $userId ),								//  add user missions
			'userProjects'	=> $this->userProjects,													//  add user projects
			'users'			=> $this->userMap,														//  add user map
			'currentDay'	=> (int) $this->session->get( $this->filterKeyPrefix.'day' ),			//  set currently selected day
		) );
	}


	protected function initDefaultFilters(){
		if( $this->session->get( 'filter.work.mission.mode' ) === NULL )
			$this->session->set( 'filter.work.mission.mode', $this->defaultFilterValues['mode'] );
		if( !$this->session->get( $this->filterKeyPrefix.'types' ) )
			$this->session->set( $this->filterKeyPrefix.'types', $this->defaultFilterValues['types'] );
		if( !$this->session->get( $this->filterKeyPrefix.'priorities' ) )
			$this->session->set( $this->filterKeyPrefix.'priorities', $this->defaultFilterValues['priorities'] );
		if( !$this->session->get( $this->filterKeyPrefix.'states' ) ){
//			$tense		= $this->session->get( $this->filterKeyPrefix.'tense' );
			$states		= $this->defaultFilterValues['states'];
			$this->session->set( $this->filterKeyPrefix.'states', $states );
		}
		if( !$this->session->get( $this->filterKeyPrefix.'projects' ) )
			$this->session->set( $this->filterKeyPrefix.'projects', array_keys( $this->userProjects ) );
		if( $this->session->get( $this->filterKeyPrefix.'order' ) === NULL ){
			if( $this->session->get( $this->filterKeyPrefix.'direction' ) === NULL ){
//				$tense		= $this->session->get( $this->filterKeyPrefix.'tense' );
				$order		= $this->defaultFilterValues['order'];
				$direction	= $this->defaultFilterValues['direction'];
				$this->session->set( $this->filterKeyPrefix.'order', $order );
				$this->session->set( $this->filterKeyPrefix.'direction', $direction );
			}
		}
	}

	protected function recoverFilters( $userId ){
		$model	= new Model_Mission_Filter( $this->env );
		$serial	= $model->getByIndex( 'userId', $userId, 'serial' );
//	print_m( $serial );
//	print_m( unserialize( $serial ) );
//	die;
//	$this->env->getMessenger()->noteNotice( '<xmp>'.$serial.'</xmp>' );
		$serial	= $serial ? unserialize( $serial ) : NULL;
		if( is_array( $serial ) ){
			foreach( $serial as $key => $value )
				$this->session->set( 'filter.work.mission.'.$key, $value );
			$this->env->getMessenger()->noteNotice( 'Filter für Aufgaben aus der letzten Sitzung wurden reaktiviert.' );
			$this->restart( NULL, TRUE );
		}
	}

	protected function saveFilters( $userId ){
		$model		= new Model_Mission_Filter( $this->env );
		$serial		= serialize( $this->session->getAll( 'filter.work.mission.' ) );
		$data		= array( 'serial' => $serial, 'timestamp' => time() );
		$indices	= array( 'userId' => $userId );
		$filter		= $model->getByIndex( 'userId', $userId );
		if( $filter )
			$model->edit( $filter->missionFilterId, $data );
		else
			$model->add( $data + $indices );
	}

	public function setFilter( $name, $value = NULL, $set = FALSE ){
		$sessionPrefix	= $this->getModeFilterKeyPrefix();
		$values			= $this->session->get( $sessionPrefix.$name );
		if( is_array( $values ) ){
			if( $set )
				$values[]	= $value;
			else if( ( $pos = array_search( $value, $values ) ) >= 0 )
				unset( $values[$pos] );
		}
		else
			$values	= $value;
		$this->session->set( $sessionPrefix.$name, $values );
		$userId		= $this->session->get( 'userId' );
		$this->saveFilters( $userId );
		if( $this->env->getRequest()->isAjax() ){
			header( 'Content-Type: application/json' );
			print( json_encode( TRUE ) );
			exit;
		}
		$this->restart( NULL, TRUE );
	}

	public function setPriority( $missionId, $priority, $showMission = FALSE ){
		$this->checkIsEditor( $missionId );
		$this->model->edit( $missionId, array( 'priority' => $priority ) );							//  store new priority
		if( !$showMission )																			//  back to list
			$this->restart( NULL, TRUE );															//  jump to list
		$this->restart( 'edit/'.$missionId, TRUE );													//  otherwise jump to or stay in mission
	}

	public function setStatus( $missionId, $status, $showMission = FALSE ){
		$this->checkIsEditor( $missionId );
		$this->model->edit( $missionId, array( 'status' => $status ) );								//  store new status
		if( $status < 0 || !$showMission )															//  mission aborted/done or back to list
			$this->restart( NULL, TRUE );															//  jump to list
		$this->restart( 'edit/'.$missionId, TRUE );													//  otherwise jump to or stay in mission
	}

	/**
	 *	Switch tense move between archive(0), current(1) and future(1)
	 *	@access		public
	 *	@param		integer		$tense			Tense: 0 - archive, 1 - current, 2 - future
	 *	@return		void		Restarts application after change in session
	 *	@deprecated	if other tenses are implemented
	 *	@todo		to be removed if other tenses are implemented
	 */
	public function switchTense( $tense = 1 ){
		$tense	= max( 0, min( 2, (int) $tense ) );
		$this->session->set( 'filter.work.mission.tense', $tense );
		$this->session->set( 'filter.work.mission.states', array() );
		$this->session->set( 'filter.work.mission.direction', NULL );
		$this->session->set( 'filter.work.mission.order', NULL );
		$this->restart( NULL, TRUE );
	}

	public function view( $missionId ){
		$config			= $this->env->getConfig();
		$words			= (object) $this->getWords( 'edit' );
		$userId			= $this->session->get( 'userId' );

		$mission	= $this->model->get( $missionId );
		if( !$mission )
			$this->messenger->noteError( $words->msgInvalidId );
		if( $this->useProjects ){
			if( !array_key_exists( $mission->projectId, $this->userProjects ) )
				$this->messenger->noteError( $words->msgInvalidProject );
		}
		if( $this->messenger->gotError() )
			$this->restart( NULL, TRUE );

		$title		= $this->request->get( 'title' );
		$dayStart	= $this->request->get( 'dayStart' );
		$dayEnd		= $this->request->get( 'dayEnd' );
		if( $this->request->get( 'type' ) == 0 ){
			$dayStart	= $this->logic->getDate( $this->request->get( 'dayWork' ) );
			$dayEnd		= $this->request->get( 'dayDue' ) ? $this->logic->getDate( $this->request->get( 'dayDue' ) ) : NULL;
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
			$this->addData( 'userProjects', $this->userProjects );
		}
		$this->addData( 'missionUsers', $missionUsers );

		if( $this->useIssues ){
			$this->env->getLanguage()->load( 'work/issue' );
			$this->addData( 'wordsIssue', $this->env->getLanguage()->getWords( 'work/issue' ) );
		}
	}

	public function testMail( $type, $send = FALSE ){
		switch( $type ){
			case "daily":																			//  
				$userId			= $this->session->get( 'userId' );										//  

				$modelUser		= new Model_User( $this->env );										//  
				$modelMission	= new Model_Mission( $this->env );									//  
				$user			= $modelUser->get( $userId );										//  

				$groupings	= array( 'missionId' );													//  group by mission ID to apply HAVING clause
				$havings	= array(																//  apply filters after grouping
					'ownerId = '.(int) $user->userId,												//  
					'workerId = '.(int) $user->userId,												//  
				);
				if( $this->env->getModules()->has( 'Manage_Projects' ) ){							//  look for module
					$modelProject	= new Model_Project( $this->env );								//  
					$userProjects	= $modelProject->getUserProjects( $user->userId );				//  get projects assigned to user
					if( $userProjects )																//  projects found
						$havings[]	= 'projectId IN ('.join( ',', array_keys( $userProjects ) ).')';//  add to HAVING clause
				}
				$havings	= array( join( ' OR ', $havings ) );									//  render HAVING clause

				//  --  TASKS  --  //
				$filters	= array(																//  task filters
					'type'		=> 0,																//  tasks only
					'status'	=> array( 0, 1, 2, 3 ),												//  states: new, accepted, progressing, ready
					'dayStart'	=> "<=".date( "Y-m-d", time() ),									//  present and past (overdue)
				);
				$order	= array( 'priority' => 'ASC' );
				$tasks	= $modelMission->getAll( $filters, $order, NULL, NULL, $groupings, $havings );	//  get filtered tasks ordered by priority

				//  --  EVENTS  --  //
				$filters	= array(																//  event filters
					'type'		=> 1,																//  events only
					'status'	=> array( 0, 1, 2, 3 ),												//  states: new, accepted, progressing, ready
					'dayStart'	=> "<=".date( "Y-m-d", time() ),									//  starting today
				);
				$order	= array( 'timeStart' => 'ASC' );
				$events	= $modelMission->getAll( $filters, $order, NULL, NULL, $groupings, $havings );	//  get filtered events ordered by start time

				if( !$events && !$tasks )															//  user has neither tasks nor events
					continue;																		//  do not send a mail, leave user alone

				$data		= array( 'user' => $user, 'tasks' => $tasks, 'events' => $events );		//  data for mail upcoming object
				$mail		= new Mail_Work_Mission_Daily( $this->env, $data );						//  create mail and populate data
				$content	= print( $mail->content );
				break;
			default:
				throw new InvalidArgumentException( 'Invalid mail type' );
		}
		print( $content );
		exit;
	}

	public function testMailNew( $missionId ){
		$data	= array(
            'mission'   => $this->model->get( $missionId ),
            'user'      => $this->userMap[$this->session->get( 'userId' )],
        );
		$mail	= new Mail_Work_Mission_New( $this->env, $data );
		print( $mail->renderBody( $data ) );
		die;
	}

	public function testMailUpdate( $missionId ){
		$missionOld		= $this->model->get( $missionId );
		$missionNew		= clone( $missionOld );
		$missionOld->status = 1;
		$missionNew->type = "1";
		$missionOld->priority = "1";
		$missionNew->dayStart	= date( "Y-m-d", strtotime( $missionNew->dayStart ) - 3600 * 24 );
		$data		= array(
			'missionBefore'	=> $missionOld,
			'missionAfter'	=> $missionNew,
			'user'			=> $this->userMap[$this->session->get( 'userId' )],
		);
		$mail	= new Mail_Work_Mission_Update( $this->env, $data );
		print( $mail->renderBody( $data ) );
		die;
	}
}
?>
