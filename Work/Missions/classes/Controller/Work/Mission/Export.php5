<?php

use CeusMedia\Common\FS\File\ICal\Builder as IcalFileBuilder;
use CeusMedia\Common\FS\File\ICal\Parser as IcalFileParser;
use CeusMedia\Common\XML\DOM\Node as XmlNode;

class Controller_Work_Mission_Export extends Controller_Work_Mission
{
	protected $pathLogs;

	public function ical()
	{
		$method		= $this->request->getMethod();
		$logFile	= $this->pathLogs.'work.mission.ical.method.log';
		$logMessage	= date( "Y-m-d H:i:s" ).' ['.$method.'] '.getEnv( 'HTTP_USER_AGENT' )."\n";
		error_log( $logMessage, 3, $logFile );
		if( !$this->userId ){
			$auth	= new BasicAuthentication( $this->env, 'iCal Export' );
			$this->userId	= $auth->authenticate();
		}
		try{
			switch( strtoupper( $method ) ){
				case 'PUT':
//					$ical	= file_get_contents( "php://input" );							//  read PUT data
					$ical	= $this->request->getBody();									//  get PUT content from request body
					$this->importFromIcal( $ical );											//  import
					break;
				case 'GET':
				default:
					$ical		= $this->exportAsIcal();
					if( $this->request->has( 'download' ) ){
						$fileName	= 'ical_'.date( 'Ymd' ).'.ics';
						Net_HTTP_Download::sendString( $ical , $fileName );					//  deliver downloadable file
					}
					else{
						$mimeType	= "text/calendar";
						$mimeType	= "text/plain;charset=utf-8";
						header( "Content-type: ".$mimeType );
						header( "Last-Modified: ".date( 'r' ) );
						print( $ical );
					}
			}
		}
		catch( Exception $e ){
			$lines	= array(
				str_repeat( "-". 78 ),
				"Date: ".date( "Y-m-d H:i:s" ),
				"Request: ".$method." ".$this->request->get( '__path' ),
				"Error: ".$e->getMessage(),
				"Agent: ".getEnv( 'HTTP_USER_AGENT' ),
			);
			$logFile	= $this->pathLogs."work.missions.ical.error.log";
			$logMessage	= join( "\n", $lines )."\n";
			error_log( $logMessage, 3, $logFile );
		}
		exit;
	}

	public function index( $format = NULL, $debug = FALSE )
	{
		$this->restart( './work/mission/help/sync' );
/*
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
*/	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		parent::__onInit();
		$this->pathLogs		= $this->env->getConfig()->get( 'path.logs' );
//		$this->logPrefix	= 'work.mission.ical.export.log';
	}

	protected function exportAsIcal()
	{
		$conditions	= array( 'status' => array( 0, 1, 2, 3 ) );
		$orders		= array( 'dayStart' => 'ASC' );
		$missions	= $this->getUserMissions( $conditions, $orders );

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

		$root		= new XmlNode( 'event');
		$calendar	= new XmlNode( 'VCALENDAR' );
		$calendar->addChild( new XmlNode( 'VERSION', '2.0' ) );
		foreach( $missions as $mission ){
			switch( $mission->type ){
				case 0:
					$date	= date( "Ymd", strtotime( $mission->dayStart ) + 24 * 60 * 60 -1 );
					$node	= new XmlNode( 'VTODO' );
					$node->addChild( new XmlNode( 'UID', md5( $mission->missionId ).'@'.$this->env->host ) );
					$node->addChild( new XmlNode( 'DUE', $date, array( 'VALUE' => 'DATE' ) ) );
					$node->addChild( new XmlNode( 'STATUS', $statesTask[$mission->status] ) );
					break;
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
		$ical	= new IcalFileBuilder();
		return trim( $ical->build( $root ) );
	}

	protected function getUserMissions( $conditions = [], $orders = [], $limits = [] )
	{
		$userProjects	= $this->logic->getUserProjects( $this->userId, TRUE );							//  get user projects from model
		$conditions['projectId']	= array_keys( $userProjects );									//
		return $this->model->getAll( $conditions, $orders, $limits );	//  return missions matched by conditions
	}

	protected function importFromIcal( $ical /*= NULL */)
	{
/*		if( !$ical && file_exists( "test.ical" ) )
			$ical	= file_get_contents( "test.ical" );
*/		$projects	= [];
		$conditions	= array( 'dayStart' => '> 0' );
		$defaultProjectId	= 0;
		foreach( $this->logic->getUserProjects( $this->userId, $conditions ) as $project ){
			if( $project->isDefault )
				$defaultProjectId	=  $project->projectId;
			$projects[$project->title]	= $project->projectId;
		}
		$missions	= [];
		$conditions	= array( 'status' => array( 0, 1, 2, 3 ) );
		$orders		= array( 'dayStart' => 'ASC' );
		foreach( $this->getUserMissions( $conditions, $orders ) as $mission )
			$missions[md5( $mission->missionId ).'@'.$this->env->host]	= $mission;

		$parser	= new IcalFileParser();
		$tree	= $parser->parse( "test", $ical );
		if( !$tree )
			return;
		$root	= @array_pop( $tree->getChildren() );
		foreach( $root->getChildren() as $node ){										//  iterate ical nodes
			if( !in_array( $node->getNodeName(), array( 'vevent', 'vtodo' ) ) )			//  neither a task nor an event
				continue;																//  go on
			$item	= [];															//  prepare empty item
			foreach( $node->getChildren() as $child )									//  iterate node's subnodes
				$item[$child->getNodeName()]	= $child->getContent();					//  note them as item attributes
			$item['type']		= $node->getNodeName();									//  note ical node type
			if( isset( $item['dtstamp'] ) ){											//  node was changed or created by client
				$item	= $this->remapCalendarItem( $item, $projects, $defaultProjectId );					//  translate ical node item to mission item
				$item['modifierId']	= $this->userId;									//  node modifing user
				if( isset( $missions[$item['uid']] ) ){									//  ical node UID is known
					$changes	= [];												//  prepare empty changes array
					$mission	= (array) $missions[$item['uid']];						//  get mission by UID
					unset( $missions[$item['uid']] );									//  remove mission from list of local missions
					foreach( $item as $key => $value )									//  iterate item attributes
						if( isset( $mission[$key] ) && $value !== $mission[$key] )		//  compare local mission and ical node item
							$changes[$key]	= $value;									//  note changed column
					if( $changes ){														//  columns have been changed
						$this->model->edit( $mission['missionId'], $changes );			//  save changes to database
//						$projectUsers	=
//						foreach( $projectUsers as $projectUser ){
							touch("update-".$this->userId);
//						}
					}
				}
				else if( $defaultProjectId ){											//  new mission and user has a default project
					$item['projectId']	= $defaultProjectId;
					$item['creatorId']	= $this->userId;
					$item['workerId']	= $this->userId;
					$item['status']		= 0;
					$this->model->add( $item );
					touch("update-".$this->userId);
				}
			}
			else{																		//  no changes in this mission
				if( isset( $missions[$item['uid']] ) )
					unset( $missions[$item['uid']] );
				else if( isset( $missions[$item['uid'].'@'.$this->env->host] ) )
					unset( $missions[$item['uid'].'@'.$this->env->host] );
			}
		}
		if( count( $missions ) === 1 ){													//  one mission has been removed
			$mission	= array_pop( $missions );										//  get this mission
			$this->model->edit( $mission->missionId, array(								//  save mission
				'modifierId'	=> $this->userId,										//  ... to be changed by User
				'modifiedAt'	=> time(),												//  ... at a time
				'status' => -2,															//  ... and set status to 'removed'
			) );
			touch("update-".$this->userId);
		}
	}

	protected function remapCalendarItem( $item, $projects, $defaultProjectId )
	{
		$data	= [];
		foreach( $item as $attribute => $content ){
			switch( $attribute ){
				case 'dtstart':
					$timestamp	= strtotime( $content );
					$data['dayStart']	= date( "Y-m-d", $timestamp );
					$data['timeStart']	= date( "H:i", $timestamp );
					break;
				case 'dtend':
					$timestamp	= strtotime( $content );
					$data['dayEnd']		= date( "Y-m-d", $timestamp );
					$data['timeEnd']	= date( "H:i", $timestamp );
					break;
				case 'due':
					$data['dayStart']	= date( "Y-m-d", strtotime( $content ) );
					break;
				case 'categories':
					$data['projectId']	= $defaultProjectId;
					foreach( explode( ",", $content ) as $category ){
						if( array_key_exists( $content, $projects ) ){
							$data['projectId']	= $projects[$content];
							break;
						}
					}
					break;
				case 'status':
					if( $content == 'CANCELLED' )
						$data['status']	= -1;
					else if( $content == 'IN-PROCESS' )
						$data['status']	= 2;
//					else if( $content == 'NEEDS-ACTION' )
//						$data['status']	= 2;
					elseif( $content == 'COMPLETED' )
						$data['status']	= 4;
					break;
				case 'summary':
					$data['title']	= $content;
					break;
				case 'priority':
					$data['priority']	= (string) round( $content / -2 + 5.5 );
					break;
				case 'created':
					$data['createdAt']	= (string) strtotime( $content );
					break;
				case 'last-modified':
					$data['modifiedAt']	= (string) strtotime( $content );
					break;
				case 'type':
					$data['type']	= (string) ( $content === "vevent" ? 1 : 0 );
					break;
				case 'uid':
				case 'location':
					$data[$attribute]	= $content;
					break;
			}
		}
		return $data;
	}
}
