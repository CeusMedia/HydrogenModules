<?php
class Controller_Work_Mission_Export extends Controller_Work_Mission{

	public function index( $format = NULL, $debug = FALSE ){
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
}
?>