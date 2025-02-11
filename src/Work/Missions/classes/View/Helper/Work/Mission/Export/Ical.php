<?php
use CeusMedia\Common\FS\File\ICal\Builder as IcalBuilder;
use CeusMedia\Common\XML\DOM\Node as XmlNode;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction as ViewHelper;

class View_Helper_Work_Mission_Export_Ical extends ViewHelper
{
	protected array $missions	= [];

	public function render(): string
	{
		$statesTask		= [
			-2		=> 'CANCELLED',
			-1		=> 'CANCELLED',
			0		=> 'NEEDS-ACTION',
			1		=> 'NEEDS-ACTION',
			2		=> 'IN-PROCESS',
			3		=> 'NEEDS-ACTION',
			4		=> 'COMPLETED',
		];

		$statesEvent	= [
			-2		=> 'CANCELLED',
			-1		=> 'CANCELLED',
			0		=> 'TENTATIVE',
			1		=> 'CONFIRMED',
			2		=> 'CONFIRMED',
			3		=> 'CONFIRMED',
			4		=> 'CONFIRMED',
		];

		$root		= new XmlNode( 'event');
		$calendar	= new XmlNode( 'VCALENDAR' );
		$calendar->addChild( new XmlNode( 'VERSION', '2.0' ) );
		foreach( $this->missions as $mission ){
			switch( $mission->type ){
				case 1:
					$node	= new XmlNode( 'VEVENT' );
					$node->addChild( new XmlNode( 'UID', md5( $mission->missionId ).'@'.$this->env->host ) );
					if( $mission->dayStart ){
						$day	= $mission->dayStart;
						if( strlen( $mission->timeStart ) )
							$day	.= ' '.$mission->timeStart;
						$datetime	= date( "Ymd\THis", strtotime( $day ) );
						$node->addChild( new XmlNode( 'DTSTART', $datetime ) );
					}
					$node->addChild( new XmlNode( 'STATUS', $statesEvent[$mission->status] ) );
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
				case 0:
				default:
					$date	= date( "Ymd", strtotime( $mission->dayStart ) + 24 * 60 * 60 -1 );
					$node	= new XmlNode( 'VTODO' );
					$node->addChild( new XmlNode( 'UID', md5( $mission->missionId ).'@'.$this->env->host ) );
					$node->addChild( new XmlNode( 'DUE', $date, ['VALUE' => 'DATE'] ) );
					$node->addChild( new XmlNode( 'STATUS', $statesTask[$mission->status] ) );
					break;
			}
			$modelProject	= new Model_Project( $this->env );
			$node->addChild( new XmlNode( 'SUMMARY', $mission->title ) );
			$node->addChild( new XmlNode( 'CREATED', date( "Ymd\THis", $mission->createdAt ) ) );
			if( $mission->modifiedAt )
				$node->addChild( new XmlNode( 'LAST-MODIFIED', date( "Ymd\THis", $mission->modifiedAt ) ) );
			if( $mission->location )
				$node->addChild( new XmlNode( 'LOCATION', $mission->location ) );
			if( $mission->priority )
				$node->addChild( new XmlNode( 'PRIORITY', round( $mission->priority * 2 - 1 ) ) );
			if( $mission->projectId )
				$node->addChild( new XmlNode( 'CATEGORIES', $modelProject->get( $mission->projectId )->title ) );
			$calendar->addChild( $node );
		}
		$root->addChild( $calendar );
		$ical	= new IcalBuilder();
		return trim( $ical->build( $root ) );
	}

	public function setMissions( array $missions ): self
	{
		$this->missions	= $missions;
		return $this;
	}
}
