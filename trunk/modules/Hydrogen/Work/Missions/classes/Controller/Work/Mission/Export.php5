<?php
class Controller_Work_Mission_Export extends Controller_Work_Mission{

	protected function exportAsIcal(){
		$userId	= $this->env->getSession()->get( 'userId' );
		if( !$userId ){
			$auth	= new BasicAuthentication( $this->env, 'Export' );
			$userId	= $auth->authenticate();
		}
		$conditions	= array( 'status' => array( 0, 1, 2, 3 ) );
		$orders		= array( 'dayStart' => 'ASC' );
		$missions	= $this->logic->getUserMissions( $userId, $conditions, $orders );

		$this->addData( 'missions', $missions );
		$this->addData( 'userId', $userId );

		$statesTask		= array(
			-2		=> 'CANCELLED',
			-1		=> 'CANCELLED',
			0		=> 'NEEDS-ACTION',
			1		=> 'NEEDS-ACTION',
			2		=> 'IN-PROCESS',
			3		=> 'NEEDS-ACTION',
			4		=> 'COMPLETED',
		);

		$statesEvent	= array(
			-2		=> 'CANCELLED',
			-1		=> 'CANCELLED',
			0		=> 'TENTATIVE',
			1		=> 'CONFIRMED',
			2		=> 'CONFIRMED',
			3		=> 'CONFIRMED',
			4		=> 'CONFIRMED',
		);

		$root		= new XML_DOM_Node( 'event');
		$calendar	= new XML_DOM_Node( 'VCALENDAR' );
		$calendar->addChild( new XML_DOM_Node( 'VERSION', '2.0' ) );
		foreach( $missions as $mission ){
			switch( $mission->type ){
				case 0:
					$date	= date( "Ymd", strtotime( $mission->dayStart ) + 24 * 60 * 60 -1 );
					$node	= new XML_DOM_Node( 'VTODO' );
					$node->addChild( new XML_DOM_Node( 'UID', md5( $mission->missionId ) ) );
					$node->addChild( new XML_DOM_Node( 'DUE', $date, array( 'VALUE' => 'DATE' ) ) );
					$node->addChild( new XML_DOM_Node( 'STATUS', $statesTask[$mission->status] ) );
					break;
				case 1:
					$node	= new XML_DOM_Node( 'VEVENT' );
					$node->addChild( new XML_DOM_Node( 'UID', md5( $mission->missionId ) ) );
					if( $mission->dayStart ){
						$day	= $mission->dayStart;
						if( strlen( $mission->timeStart ) )
							$day	.= ' '.$mission->timeStart;
						$datetime	= date( "Ymd\THis", strtotime( $day ) );
						$node->addChild( new XML_DOM_Node( 'DTSTART', $datetime ) );
					}
					$node->addChild( new XML_DOM_Node( 'STATUS', $statesEvent[$mission->status] ) );
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
			$modelProject	= new Model_Project( $this->env );
			$node->addChild( new XML_DOM_Node( 'SUMMARY', $mission->title ) );
			$node->addChild( new XML_DOM_Node( 'CREATED', date( "Ymd\THis", $mission->createdAt ) ) );
			if( $mission->modifiedAt )
				$node->addChild( new XML_DOM_Node( 'LAST-MODIFIED', date( "Ymd\THis", $mission->modifiedAt ) ) );
			if( $mission->location )
				$node->addChild( new XML_DOM_Node( 'LOCATION', $mission->location ) );
			if( $mission->priority )
				$node->addChild( new XML_DOM_Node( 'PRIORITY', round( ( $mission->priority - 5.5 ) * -2 ) ) );
			if( $mission->projectId )
				$node->addChild( new XML_DOM_Node( 'CATEGORIES', $modelProject->get( $mission->projectId )->title ) );
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

	public function ical( $debug = NULL ){
		$method		= strtoupper( getEnv( 'REQUEST_METHOD' ) );
error_log( date( "Y-m-d H:i:s" )." [".$method."]\n", 3, 'request.method.log' );
		if( $method === "GET" ){
			$ical		= $this->exportAsIcal();
			$delivery	= "return";
			switch( $delivery ){
				case "download":
					Net_HTTP_Download::sendString( $ical , 'ical_'.date( 'Ymd' ).'.ics' );          //  deliver downloadable file
					break;
				case "return":
					$mimeType	= "text/calendar";
					$mimeType	= "text/plain;charset=utf-8";
					header( "Content-type: ".$mimeType );
					header( "Last-Modified: ".date( 'r' ) );
					$debug ? xmp( $ical ) : print( $ical );
					break;
			}
			exit;
		}
		else if( $method === "PUT" ){
			$data	= file_get_contents( "php://input" );
	        file_put_contents( "put_data.txt", $data );
			exit;
		}
	}

	public function index( $format = NULL, $debug = FALSE ){
/*		switch( $format ){
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
*/	}
}
?>
