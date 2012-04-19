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

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ) {
		parent::__construct( $env );
		$this->model	= new Model_Mission( $env );
		$this->logic	= new Logic_Mission( $env );
	}

	public function add(){
		$config			= $this->env->getConfig();
		$session		= $this->env->getSession();
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= $this->getWords( 'add' );

		$content	= $request->get( 'content' );
		$status		= $request->get( 'status' );
		$dayStart	= !$request->get( 'type' ) ? $request->get( 'day' ) : $request->get( 'dayStart' );
		$dayEnd		= $request->get( 'dayEnd' );
		
		if( $request->get( 'add' ) ){
			if( !$content )
				$messenger->noteError( $words->msgNoContent );
			if( !$messenger->gotError() ){
				$data	= array(
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
	}

	public function edit( $missionId ){
		$config			= $this->env->getConfig();
		$session		= $this->env->getSession();
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= $this->getWords( 'edit' );

		$content	= $request->get( 'content' );
		$dayStart	= $request->get( 'dayStart' );
		$dayEnd		= $request->get( 'dayEnd' );
		if( $request->get( 'type' ) == 0 )
			$dayStart	= $this->logic->getDate( $request->get( 'day' ) ); 
	
		if( $request->get( 'edit' ) ){
			if( !$content )
				$messenger->noteError( $words->msgNoContent );
			if( !$messenger->gotError() ){
				$data	= array(
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
				$this->model->edit( $missionId, $data );
				$messenger->noteSuccess( $words->msgSuccess );
				$this->restart( './work/mission' );
			}
		}
		$mission	= $this->model->get( $missionId );
		$this->addData( 'mission', $mission );
	}

	public function export( $format = NULL ){
		switch( $format ){
			case 'ical':
				$missions	= $this->model->getAll( array( 'status' => array( 0, 1, 2, 3 ) ), array( 'dayStart' => 'ASC' ) );
				$root		= new XML_DOM_Node( 'event');
				$calendar	= new XML_DOM_Node( 'VCALENDAR' );
				$calendar->addChild( new XML_DOM_Node( 'VERSION', '2.0' ) );
				foreach( $missions as $mission ){
					switch( $mission->type ){
						case 0:
							$node	= new XML_DOM_Node( 'VTODO' );
							$node->addChild( new XML_DOM_Node( 'DUE', date( "Ymd", strtotime( $mission->dayStart ) + 24 * 60 * 60 -1 ), array( 'VALUE' => 'DATE' ) ) );
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
				error_log( getEnv( 'HTTP_USER_AGENT' )."\n", 3, 'ua.log' );
#				if( 1 )
#					header( 'Content-type: text/plain;charset=utf-8' );
#				if( 1 )
#					header( 'Content-type: text/calendar' );
#				else if( 1 )
#					Net_HTTP_Download::sendString( $ical , 'ical_'.date( 'Ymd' ).'.ics' );			//  deliver downloadable file
				print( $ical );
				die;
				break;
			default:
				$missions	= $this->model->getAll();												//  get all missions
				$zip		= gzencode( serialize( $missions ) );									//  gzip serial of mission objects
				Net_HTTP_Download::sendString( $zip , 'missions_'.date( 'Ymd' ).'.gz' );			//  deliver downloadable file
		}
		
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
	
	public function filter(){
		$request		= $this->env->getRequest();
		$session		= $this->env->getSession();
		if( $request->has( 'reset' ) ){
			$session->remove( 'filter_mission_query' );
			$session->remove( 'filter_mission_types' );
			$session->remove( 'filter_mission_priorities' );
			$session->remove( 'filter_mission_states' );
			$session->remove( 'filter_mission_order' );
			$session->remove( 'filter_mission_direction' );
		}
		else{
			if( $request->has( 'query' ) )
				$session->set( 'filter_mission_query', $request->get( 'query' ) );
			if( $request->has( 'types' ) )
				$session->set( 'filter_mission_types', $request->get( 'types' ) );
			if( $request->has( 'priorities' ) )
				$session->set( 'filter_mission_priorities', $request->get( 'priorities' ) );
			if( $request->has( 'states' ) )
				$session->set( 'filter_mission_states', $request->get( 'states' ) );
			if( $request->has( 'order' ) )
				$session->set( 'filter_mission_order', $request->get( 'order' ) );
			if( $request->has( 'direction' ) )
				$session->set( 'filter_mission_direction', $request->get( 'direction' ) );
		}
		$this->restart( '', TRUE );
	}

	/**
	 *	Default action on this controller.
	 *	@access		public
	 *	@return		void
	 */
	public function index(){
		$config			= $this->env->getConfig();
		$session		= $this->env->getSession();
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= $this->getWords( 'index' );

		$query		= $session->get( 'filter_mission_query' );
		$types		= $session->get( 'filter_mission_types' );
		$priorities	= $session->get( 'filter_mission_priorities' );
		$states		= $session->get( 'filter_mission_states' );
		$direction	= $session->get( 'filter_mission_direction' );
		$order		= $session->get( 'filter_mission_order' );

		print_m( $session->getAll() );
		
		$direction	= $direction ? $direction : 'ASC';
		$order		= $order ? array( $order => $direction ) : array();
		$order['content']	= 'ASC';
		
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
		
		$missions	= $this->model->getAll( $conditions, $order );
		$this->addData( 'missions', $missions );
		$this->addData( 'filterTypes', $session->get( 'filter_mission_types' ) );
		$this->addData( 'filterPriorities', $session->get( 'filter_mission_priorities' ) );
		$this->addData( 'filterStates', $session->get( 'filter_mission_states' ) );
		$this->addData( 'filterOrder', $session->get( 'filter_mission_order' ) );
		$this->addData( 'filterDirection', $direction );
	}

	public function changeDay( $missionId ){
		$date		= trim( $this->env->getRequest()->get( 'date' ) );
		$mission	= $this->model->get( $missionId );
		if( preg_match( "/^[+-][0-9]+$/", $date ) ){
			$day	= 24 * 60 * 60;
			$sign	= substr( $date, 0, 1 );
			$number	= substr( $date, 1 );
			$date	= strtotime( $mission->dayStart );
			$diff	= $sign == '+' ? $number * $day : -$number * $day;
			$date	= date( 'Y-m-d', strtotime( $mission->dayStart ) + $diff );
		}
		$data		= array( 'dayStart' => $date );
		if( $mission->dayEnd ){
			if( !isset( $diff ) )
				$diff	= strtotime( $date ) - strtotime( $mission->dayStart );
			$data['dayEnd']	= date( 'Y-m-d', strtotime( $mission->dayEnd ) + $diff );
		}
		$this->model->edit( $missionId, $data );
		$this->restart( NULL, TRUE );
	}
	
	public function setPriority( $missionId, $priority, $showMission = FALSE ){
		$this->model->edit( $missionId, array( 'priority' => $priority ) );
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
