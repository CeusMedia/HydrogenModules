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

	protected $defaultFilterValues	= array(
		'tense'		=> 1,
		'states'	=> array(
			0	=> array(																			//  archive
				Model_Mission::STATUS_ABORTED,
				Model_Mission::STATUS_REJECTED,
				Model_Mission::STATUS_FINISHED
			),
			1	=> array(																			//  current
				Model_Mission::STATUS_NEW,
				Model_Mission::STATUS_ACCEPTED,
				Model_Mission::STATUS_PROGRESS,
				Model_Mission::STATUS_READY
			),
			2	=> array(																			//  future
				Model_Mission::STATUS_NEW,
				Model_Mission::STATUS_ACCEPTED,
				Model_Mission::STATUS_PROGRESS,
				Model_Mission::STATUS_READY
			),
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
		'order'			=> array(
			0	=> 'dayStart',																		//  archive
			1	=> 'priority',																		//  current
			2	=> 'dayStart',																		//  future
		),
		'direction'		=> array(
			0	=> "DESC",																			//  archive
			1	=> "ASC",																			//  current
			2	=> "ASC",																			//  future
		)

	);

	protected function __onInit(){
		$this->model	= new Model_Mission( $this->env );
		$this->logic	= $logic	= new Logic_Mission( $this->env );
		$this->session	= $session	= $this->env->getSession();
		$this->acl		= $this->env->getAcl();

		$model			= new Model_User( $this->env );
		foreach( $model->getAll() as $user )
			$this->userMap[$user->userId]	= $user;

		$modules	= $this->env->getModules();

		$this->addData( 'useProjects', $this->useProjects = $modules->has( 'Manage_Projects' ) );
		$this->addData( 'useIssues', $this->useIssues = $modules->has( 'Manage_Issues' ) );

		$userId		= $session->get( 'userId' );
		if( $userId ){
			if( $this->hasFullAccess() )
				$this->userProjects	= $this->logic->getUserProjects( $userId );
			else
				$this->userProjects		= $this->logic->getUserProjects( $userId, TRUE );
			$this->initFilters( $userId );
		}
	}

	protected function initFilters( $userId ){
		$session	= $this->env->getSession();
		if( !(int) $userId )
			return;
		if( !$session->getAll( 'filter.work.mission.', TRUE )->count() ){
			$model	= new Model_Mission_Filter( $this->env );
			$serial	= $model->getByIndex( 'userId', $userId, 'serial' );
//$this->env->getMessenger()->noteNotice( '<xmp>'.$serial.'</xmp>' );
			$serial	= $serial ? unserialize( $serial ) : NULL;
			if( is_array( $serial ) ){
				foreach( $serial as $key => $value )
					$session->set( 'filter.work.mission.'.$key, $value );
				$this->env->getMessenger()->noteNotice( 'Filter für Aufgaben aus der letzten Sitzung wurden reaktiviert.' );
			}
		}

		//  --  DEFAULT SETTINGS  --  //
		if( $session->get( 'filter.work.mission.tense' ) === NULL )
			$session->set( 'filter.work.mission.tense', $this->defaultFilterValues['tense'] );
		else
			$session->set( 'filter.work.mission.tense', (int) $session->get( 'filter.work.mission.tense' ) );
		if( !$session->get( 'filter.work.mission.types' ) )
			$session->set( 'filter.work.mission.types', $this->defaultFilterValues['types'] );
		if( !$session->get( 'filter.work.mission.priorities' ) )
			$session->set( 'filter.work.mission.priorities', $this->defaultFilterValues['priorities'] );
		if( !$session->get( 'filter.work.mission.states' ) ){
			$tense		= $session->get( 'filter.work.mission.tense' );
			$states		= $this->defaultFilterValues['states'][$tense];
			$session->set( 'filter.work.mission.states', $states );
		}
		if( !$session->get( 'filter.work.mission.projects' ) )
			$session->set( 'filter.work.mission.projects', array_keys( $this->userProjects ) );
		if( $session->get( 'filter.work.mission.order' ) === NULL ){
			if( $session->get( 'filter.work.mission.direction' ) === NULL ){
				$tense		= $session->get( 'filter.work.mission.tense' );
				$order		= $this->defaultFilterValues['order'][$tense];
				$direction	= $this->defaultFilterValues['direction'][$tense];
				$session->set( 'filter.work.mission.order', $order );
				$session->set( 'filter.work.mission.direction', $direction );
			}
		}

		//  --  GENERAL LOGIC CONDITIONS  --  //
		$tense		= $session->get( 'filter.work.mission.tense' );
		$this->logic->generalConditions['status']		= $this->defaultFilterValues['states'][$tense];
		switch( $tense ){
			case 1:
				$this->logic->generalConditions['dayStart']	= '<'.date( "Y-m-d", time() + 7 * 24 * 60 * 60 );				//  @todo: kriss: calculation is incorrect
				break;
			case 2:
				$this->logic->generalConditions['dayStart']	= '>='.date( "Y-m-d", time() + 6 * 24 * 60 * 60 );				//  @todo: kriss: calculation is incorrect
				break;
		}
	}

	public function setFilter( $name, $value = NULL, $set = FALSE ){
		$session	= $this->env->getSession();
		$values		= $session->get( 'filter.work.mission.'.$name );
		if( is_array( $values ) ){
			if( $set )
				$values[]	= $value;
			else if( ( $pos = array_search( $value, $values ) ) >= 0 )
				unset( $values[$pos] );
		}
		else{
			$values	= $value;
		}
		$session->set( 'filter.work.mission.'.$name, $values );
		$userId		= $session->get( 'userId' );
		$model		= new Model_Mission_Filter( $this->env );
		$serial		= serialize( $session->getAll( 'filter.work.mission.' ) );
		$data		= array( 'serial' => $serial, 'timestamp' => time() );
		$indices	= array( 'userId' => $userId );
		$filter		= $model->getByIndex( 'userId', $userId );
		if( $filter )
			$model->edit( $filter->missionFilterId, $data );
		else
			$model->add( $data + $indices );
		if( $this->env->getRequest()->isAjax() ){
			header( 'Content-Type: application/json' );
			print( json_encode( TRUE ) );
			exit;
		}
		$this->restart( NULL, TRUE );
	}

	public function add(){
		$config			= $this->env->getConfig();
		$session		= $this->env->getSession();
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'add' );
		$userId			= $session->get( 'userId' );

		$title		= $request->get( 'title' );
		$status		= $request->get( 'status' );
		$dayStart	= !$request->get( 'type' ) ? $request->get( 'dayWork' ) : $request->get( 'dayStart' );
		$dayEnd		= !$request->get( 'type' ) ? $request->get( 'dayDue' ) : $request->get( 'dayEnd' );

		if( $request->has( 'add' ) ){
			if( !$title )
				$messenger->noteError( $words->msgNoTitle );
			if( !$messenger->gotError() ){
				$data	= array(
					'ownerId'			=> (int) $userId,
					'workerId'			=> (int) $request->get( 'workerId' ),
					'projectId'			=> (int) $request->get( 'projectId' ),
					'type'				=> (int) $request->get( 'type' ),
					'priority'			=> (int) $request->get( 'priority' ),
					'status'			=> $status,
					'title'				=> $title,
					'content'			=> $request->get( 'content' ),
					'dayStart'			=> $this->logic->getDate( $dayStart ),
					'dayEnd'			=> $this->logic->getDate( $dayEnd ),
					'timeStart'			=> $request->get( 'timeStart' ),
					'timeEnd'			=> $request->get( 'timeEnd' ),
					'minutesProjected'	=> $this->getMinutesFromInput( $request->get( 'minutesProjected' ) ),
					'location'			=> $request->get( 'location' ),
					'reference'			=> $request->get( 'reference' ),
					'createdAt'			=> time(),
				);
				$missionId	= $this->model->add( $data );
				$messenger->noteSuccess( $words->msgSuccess );
				$this->logic->noteChange( 'new', $missionId, NULL, $userId );
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
		$mission['minutesProjected']	= $this->getMinutesFromInput( $request->get( 'minutesProjected' ) );
		$this->addData( 'mission', (object) $mission );
		$this->addData( 'users', $this->userMap );
		$this->addData( 'userId', $userId );
		$this->addData( 'day', (int) $session->get( 'filter.work.mission.day' ) );

		if( $this->useProjects )
			$this->addData( 'userProjects', $this->userProjects );
	}

	public function ajaxRenderList( $date = NULL ){
		$session	= $this->env->getSession();
		$words		= $this->getWords();

		$day		= (int) $session->get( 'filter.work.mission.day' );

		$userId		= $session->get( 'userId' );
		$missions	= $this->getFilteredMissions( $userId );

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

/*	public function ajaxRenderLists(){
		$session	= $this->env->getSession();
		$words		= $this->getWords();

		$userId		= $session->get( 'userId' );
		$missions	= $this->getFilteredMissions( $userId );

		$helper		= new View_Helper_Work_Mission_List_Days( $this->env );
		$helper->setMissions( $missions );
		$helper->setWords( $words );

		$buttons	 = new View_Helper_Work_Mission_List_DayControls( $this->env );
		$buttons->setWords( $words );
		$buttons->setDayMissions( $helper->getDayMissions() );
		$buttons	= $buttons->render();
		$lists		= $helper->render();															//  render day lists
		print( json_encode( array( 'buttons' => $buttons, 'lists' => $lists ) ) );
		exit;
	}*/

	public function ajaxSaveContent( $missionId ){
		$data		= array(
			'content'	=> $this->env->getRequest()->get( 'content' ),
		);
		$this->model->edit( $missionId, $data, FALSE );
		exit;
	}

	public function ajaxSelectDay( $day ){
		$this->env->getSession()->set( 'filter.work.mission.day', (int) $day );
		$this->ajaxRenderList();
//		print( json_encode( (int) $day ) );
//		exit;
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

	public function close( $missionId ){
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$userId			= $this->env->getSession()->get( 'userId' );
		$words			= (object) $this->getWords( 'edit' );
		$data			= array(
			'status'		=> $request->get( 'status' ),
			'hoursRequired'	=> $request->get( 'hoursRequired' ),
		);
		$mission		= $this->model->get( $missionId );
		$this->model->edit( $missionId, $data );
		$this->logic->noteChange( 'update', $missionId, $mission, $userId );
		$messenger->noteSuccess( $words->msgSuccessClosed );
		$this->restart( NULL, TRUE );
	}

	public function edit( $missionId ){
		$config			= $this->env->getConfig();
		$session		= $this->env->getSession();
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'edit' );
		$userId			= $session->get( 'userId' );

		$mission		= $this->model->get( $missionId );
		if( !$mission )
			$messenger->noteError( $words->msgInvalidId );
		if( $this->useProjects ){
			if( !array_key_exists( $mission->projectId, $this->userProjects ) )
				$messenger->noteError( $words->msgInvalidProject );
		}
		if( $messenger->gotError() )
			$this->restart( NULL, TRUE );

		$title		= $request->get( 'title' );
		$dayStart	= $request->get( 'dayStart' );
		$dayEnd		= $request->get( 'dayEnd' );
		if( $request->get( 'type' ) == 0 ){
			$dayStart	= $this->logic->getDate( $request->get( 'dayWork' ) );
			$dayEnd		= $request->get( 'dayDue' ) ? $this->logic->getDate( $request->get( 'dayDue' ) ) : NULL;
		}

		if( $request->get( 'edit' ) ){
			if( !$title )
				$messenger->noteError( $words->msgNoTitle );
			if( !$messenger->gotError() ){
				$data	= array(
					'workerId'			=> (int) $request->get( 'workerId' ),
					'projectId'			=> (int) $request->get( 'projectId' ),
					'type'				=> (int) $request->get( 'type' ),
					'priority'			=> (int) $request->get( 'priority' ),
					'title'				=> $title,
//					'content'			=> $request->get( 'content' ),
					'status'			=> (int) $request->get( 'status' ),
					'dayStart'			=> $dayStart,
					'dayEnd'			=> $dayEnd,
					'timeStart'			=> $request->get( 'timeStart' ),
					'timeEnd'			=> $request->get( 'timeEnd' ),
					'minutesProjected'	=> $this->getMinutesFromInput( $request->get( 'minutesProjected' ) ),
					'minutesRequired'	=> $this->getMinutesFromInput( $request->get( 'minutesRequired' ) ),
//					'hoursProjected'	=> $request->get( 'hoursProjected' ) ? $request->get( 'hoursProjected' ) : NULL,
//					'hoursRequired'		=> $request->get( 'hoursRequired' ) ? $request->get( 'hoursRequired' ) : NULL,
					'location'			=> $request->get( 'location' ),
					'reference'			=> $request->get( 'reference' ),
					'modifiedAt'		=> time(),
				);
print_m( $data );
				$this->model->edit( $missionId, $data, FALSE );
				$messenger->noteSuccess( $words->msgSuccess );
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

	protected function hasFullAccess(){
		return $this->env->getAcl()->hasFullAccess( $this->env->getSession()->get( 'roleId' ) );
	}

	public function filter(){
		$request		= $this->env->getRequest();
		$session		= $this->env->getSession();
		if( $request->has( 'reset' ) ){
			$session->remove( 'filter.work.mission.access' );
			$session->remove( 'filter.work.mission.query' );
			$session->remove( 'filter.work.mission.types' );
			$session->remove( 'filter.work.mission.priorities' );
			$session->remove( 'filter.work.mission.states' );
			$session->remove( 'filter.work.mission.projects' );
			$session->remove( 'filter.work.mission.order' );
			$session->remove( 'filter.work.mission.direction' );
			$session->remove( 'filter.work.mission.day' );
		}
		if( $request->has( 'access' ) )
			$session->set( 'filter.work.mission.access', $request->get( 'access' ) );
		if( $request->has( 'query' ) )
			$session->set( 'filter.work.mission.query', $request->get( 'query' ) );
		if( $request->has( 'types' ) )
			$session->set( 'filter.work.mission.types', $request->get( 'types' ) );
		if( $request->has( 'priorities' ) )
			$session->set( 'filter.work.mission.priorities', $request->get( 'priorities' ) );
		if( $request->has( 'states' ) )
			$session->set( 'filter.work.mission.states', $request->get( 'states' ) );
		if( $request->has( 'projects' ) )
			$session->set( 'filter.work.mission.projects', $request->get( 'projects' ) );
		if( $request->has( 'order' ) )
			$session->set( 'filter.work.mission.order', $request->get( 'order' ) );
		if( $request->has( 'direction' ) )
			$session->set( 'filter.work.mission.direction', $request->get( 'direction' ) );
#			if( $request->has( 'direction' ) )
#				$session->set( 'filter.work.mission.direction', $request->get( 'direction' ) );
		if( $request->isAjax() ){
			print( json_encode( (object) array( 'session' => $session->getAll(), 'request' => $request->getAll() ) ) );
			 exit;
		}
		$this->restart( '', TRUE );
//		$request->isAjax() ? exit : $this->restart( '', TRUE );
	}

	protected function getFilteredMissions( $userId, $additionalConditions = array() ){
//		$config			= $this->env->getConfig();
		$session		= $this->env->getSession();
//		$userId			= $session->get( 'userId' );

		$query		= $session->get( 'filter.work.mission.query' );
		$types		= $session->get( 'filter.work.mission.types' );
		$priorities	= $session->get( 'filter.work.mission.priorities' );
		$states		= $session->get( 'filter.work.mission.states' );
		$projects	= $session->get( 'filter.work.mission.projects' );
		$direction	= $session->get( 'filter.work.mission.direction' );
		$order		= $session->get( 'filter.work.mission.order' );
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
		return $this->logic->getUserMissions( $userId, $conditions, $orders );
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

		$access		= $session->get( 'filter.work.mission.access' );
		$direction	= $session->get( 'filter.work.mission.direction' );
		$order		= $session->get( 'filter.work.mission.order' );
		if( !$order )
			$this->restart( './work/mission/filter?order=priority' );
		if( !$access )
			$this->restart( './work/mission/filter?access=worker' );

		$direction	= $direction ? $direction : 'ASC';
		$session->set( 'filter.work.mission.direction', $direction );

		$this->setData( array(																		//  assign data to view
			'missions'		=> $this->getFilteredMissions( $userId ),								//  add user missions
			'userProjects'	=> $this->userProjects,												//  add user projects
			'users'			=> $this->userMap,														//  add user map
			'currentDay'	=> (int) $session->get( 'filter.work.mission.day' ),							//  set currently selected day
		) );

		$this->addData( 'filterAccess', $access );
		$this->addData( 'filterTypes', $session->get( 'filter.work.mission.types' ) );
		$this->addData( 'filterPriorities', $session->get( 'filter.work.mission.priorities' ) );
		$this->addData( 'filterStates', $session->get( 'filter.work.mission.states' ) );
		$this->addData( 'filterOrder', $order );
		$this->addData( 'filterProjects', $session->get( 'filter.work.mission.projects' ) );
		$this->addData( 'filterDirection', $direction );
		$this->addData( 'filterTense', $session->get( 'filter.work.mission.tense' ) );
		$this->addData( 'filterQuery', $session->get( 'filter.work.mission.query' ) );
		$this->addData( 'defaultFilterValues', $this->defaultFilterValues );
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

	/**
	 *	Switch tense move between archive(0), current(1) and future(1)
	 *	@access		public
	 *	@param		integer		$tense			Tense: 0 - archive, 1 - current, 2 - future
	 *	@return		void		Restarts application after change in session
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
		$session		= $this->env->getSession();
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'edit' );
		$userId			= $session->get( 'userId' );

		$mission	= $this->model->get( $missionId );
		if( !$mission )
			$messenger->noteError( $words->msgInvalidId );
		if( $this->useProjects ){
			if( !array_key_exists( $mission->projectId, $this->userProjects ) )
				$messenger->noteError( $words->msgInvalidProject );
		}
		if( $messenger->gotError() )
			$this->restart( NULL, TRUE );

		$title		= $request->get( 'title' );
		$dayStart	= $request->get( 'dayStart' );
		$dayEnd		= $request->get( 'dayEnd' );
		if( $request->get( 'type' ) == 0 ){
			$dayStart	= $this->logic->getDate( $request->get( 'dayWork' ) );
			$dayEnd		= $request->get( 'dayDue' ) ? $this->logic->getDate( $request->get( 'dayDue' ) ) : NULL;
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
				$session		= $this->env->getSession();											//  
				$messenger		= $this->env->getMessenger();										//  
				$userId			= $session->get( 'userId' );										//  

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
