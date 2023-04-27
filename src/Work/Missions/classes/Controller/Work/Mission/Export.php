<?php

use CeusMedia\Common\FS\File\ICal\Parser as IcalFileParser;
use CeusMedia\Common\Net\HTTP\Download as HttpDownload;

class Controller_Work_Mission_Export extends Controller_Work_Mission
{
	protected ?string $pathLogs		= NULL;

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
					$ical	= $this->request->getRawPostData();								//  get PUT content from request body
					$this->importFromIcal( $ical );											//  import
					break;
				case 'GET':
				default:
					$ical		= $this->exportAsIcal();
					if( $this->request->has( 'download' ) ){
						$fileName	= 'ical_'.date( 'Ymd' ).'.ics';
						HttpDownload::sendString( $ical , $fileName );					//  deliver downloadable file
					}
					else{
//						$mimeType	= "text/calendar";
						$mimeType	= "text/plain;charset=utf-8";
						header( "Content-type: ".$mimeType );
						header( "Last-Modified: ".date( 'r' ) );
						print( $ical );
					}
			}
		}
		catch( Exception $e ){
			$lines	= array(
				str_repeat( "-", 78 ),
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

	public function index( string $missionId = NULL ): void
	{
		$this->restart( './work/mission/help/sync' );
	}

	//  --  PROTECTED  --  //

	protected function __onInit(): void
	{
		parent::__onInit();
		$this->pathLogs		= $this->env->getConfig()->get( 'path.logs' );
//		$this->logPrefix	= 'work.mission.ical.export.log';
	}

	protected function exportAsIcal(): string
	{
		$conditions		= ['status' => [0, 1, 2, 3]];
		$orders			= ['dayStart' => 'ASC'];
		$missions		= $this->getUserMissions($conditions, $orders);

		$helper = new View_Helper_Work_Mission_Export_Ical();
		$helper->setEnv( $this->env );
		$helper->setMissions($missions);
		return $helper->render();
	}

	protected function getUserMissions( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$userProjects	= $this->logic->getUserProjects( $this->userId, TRUE );							//  get user projects from model
		$conditions['projectId']	= array_keys( $userProjects );									//
		return $this->model->getAll( $conditions, $orders, $limits );	//  return missions matched by conditions
	}

	protected function importFromIcal( string $ical )
	{
/*		if( !$ical && file_exists( "test.ical" ) )
			$ical	= file_get_contents( "test.ical" );
*/		$projects	= [];
		$defaultProjectId	= 0;
		foreach( $this->logic->getUserProjects( $this->userId, TRUE ) as $project ){
			if( $project->isDefault )
				$defaultProjectId	=  $project->projectId;
			$projects[$project->title]	= $project->projectId;
		}
		$missions	= [];
		$conditions	= ['status' => [0, 1, 2, 3]];
		$orders		= ['dayStart' => 'ASC'];
		foreach( $this->getUserMissions( $conditions, $orders ) as $mission )
			$missions[md5( $mission->missionId ).'@'.$this->env->host]	= $mission;

		$parser	= new IcalFileParser();
		$tree	= $parser->parse( "test", $ical );
		$nodes	= $tree->getChildren();
		$root	= @array_pop( $nodes );
		foreach( $root->getChildren() as $node ){										//  iterate ical nodes
			if( !in_array( $node->getNodeName(), ['vevent', 'vtodo'] ) )			//  neither a task nor an event
				continue;																//  go on
			$item	= [];															//  prepare empty item
			foreach( $node->getChildren() as $child )									//  iterate node's subnodes
				$item[$child->getNodeName()]	= $child->getContent();					//  note them as item attributes
			$item['type']		= $node->getNodeName();									//  note ical node type
			if( isset( $item['dtstamp'] ) ){											//  node was changed or created by client
				$item	= $this->remapCalendarItem( $item, $projects, $defaultProjectId );					//  translate ical node item to mission item
				$item['modifierId']	= $this->userId;									//  node modifying user
				if( isset( $missions[$item['uid']] ) ){									//  ical node UID is known
					$changes	= [];													//  prepare empty changes array
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

	protected function remapCalendarItem( $item, array $projects, string $defaultProjectId ): array
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
						if( array_key_exists( $category, $projects ) ){
							$data['projectId']	= $projects[$category];
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
