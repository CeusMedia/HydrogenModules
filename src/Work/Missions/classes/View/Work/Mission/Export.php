<?php

use CeusMedia\Common\FS\File\ICal\Builder as IcalFileBuilder;
use CeusMedia\Common\XML\DOM\Node as XmlNode;
use CeusMedia\HydrogenFramework\View;

class View_Work_Mission_Export extends View{

	public function ical(){
		$request	= $this->env->getRequest();
		$root		= new XmlNode( 'event');
		$calendar	= new XmlNode( 'VCALENDAR' );
		$calendar->addChild( new XmlNode( 'VERSION', '2.0' ) );
		foreach( $missions as $mission ){
			switch( $mission->type ){
				case 0:
					$date	= date( "Ymd", strtotime( $mission->dayStart ) + 24 * 60 * 60 -1 );
					$node	= new XmlNode( 'VTODO' );
					$node->addChild( new XmlNode( 'DUE', $date, ['VALUE' => 'DATE'] ) );
#					$node->addChild( new XmlNode( 'STATUS', 'NEEDS-ACTION' ) );
					break;
				case 1:
					$node	= new XmlNode( 'VEVENT' );
					if( $mission->dayStart ){
						$day	= $mission->dayStart;
						if( strlen( $mission->timeStart ) )
							$day	.= ' '.$mission->timeStart;
						$datetime	= date( "Ymd\THis", strtotime( $day ) );
						$node->addChild( new XmlNode( 'DTSTART', $datetime ) );
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
						$node->addChild( new XmlNode( 'DTEND', $datetime ) );
					}
					break;
			}
			$node->addChild( new XmlNode( 'SUMMARY', $mission->title ) );
			$node->addChild( new XmlNode( 'CREATED', date( "Ymd\THis", $mission->createdAt ) ) );
			if( $mission->modifiedAt )
				$node->addChild( new XmlNode( 'LAST-MODIFIED', date( "Ymd\THis", $mission->modifiedAt ) ) );
			if( $mission->location )
				$node->addChild( new XmlNode( 'LOCATION', $mission->location ) );
			if( $mission->priority ){
//				$priority	= ceil( $mission->priority - 7 ) / -2;
				$priority	= $mission->priority > 3 ? 3 : ( $mission->priority < 3 ? 1 : 2 );
				$node->addChild( new XmlNode( 'PRIORITY', $priority ) );
			}
			$calendar->addChild( $node );
		}
		$root->addChild( $calendar );
		$ical	= new IcalFileBuilder();
		$ical	= trim( $ical->build( $root ) );
		error_log( date( 'Y-m-d H:i:s' ).' | '.getEnv( 'REMOTE_ADDR' ).': '.getEnv( 'HTTP_USER_AGENT' )."\n", 3, 'ua.log' );
		return $ical;
	}
/*
	public function index(){
		return $this->loadContentFile( 'html/work/mission/export.html' );
	}*/
}
?>
