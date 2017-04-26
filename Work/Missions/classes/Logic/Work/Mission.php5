<?php
class Logic_Work_Mission{

	static protected $instance;

	public $timeOffset			= 0; # nerd mode: 4 hours night shift: 14400;
	public $generalConditions	= array();
	protected $modelMission;
	protected $modelVersion;
	protected $modelChange;
	protected $modelDocument;
	public $useProjects			= TRUE;																//  @deprecated: projects module is required by now

	/**
	 *	Constructor. Protected to force singleton use.
	 *	@access		protected
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env		Hydrogen framework environment object
	 *	@return		void
	 */
	protected function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env				= $env;
		$this->modelMission		= new Model_Mission( $env );
		$this->modelVersion		= new Model_Mission_Version( $this->env );
		$this->modelChange		= new Model_Mission_Change( $this->env );
		$this->modelDocument	= new Model_Mission_Document( $this->env );
	}

	/**
	 *	Cloning is disabled to force singleton use.
	 *	@access		protected
	 *	@return		void
	 */
	protected function __clone(){}

	/**
	 *	Get singleton instance of logic.
	 *	@static
	 *	@access		public
	 *	@return		object			Singleton instance of logic
	 */
	static public function getInstance( $env ){
		if( !self::$instance )
			self::$instance	= new self( $env );
		return self::$instance;
	}

	public function getDate( $string ){
		$day	= 24 * 60 * 60;
		$now	= time();
		$string	= strtolower( trim( $string ) );

		if( preg_match( "/^[+-][0-9]+$/", $string ) ){
			$sign	= substr( $string, 0, 1 );
			$number	= substr( $string, 1 );
			$time	= $sign == '+' ? $now + $number * $day : $now - $number * $day;
		}
		else{
			switch( $string ){
				case '':
				case 'heute':
					$time	= $now;
					break;
				case '+1':
				case 'morgen':
					$time	= $now + 1 * $day;
					break;
				case '+2':
				case 'übermorgen':
					$time	= $now + 1 * $day;
					break;
				default:
					$time	= strtotime( $string );
					break;
			}
		}
		return date( "Y-m-d", $time );
	}

/*	public function getDocumentsOfMission( $missionId, $orders ){
		$model		= new Model_Mission_Document( $this->env );
		return $model->getAllByIndex( 'missionId', $missionId, $orders );
	}*/

	public function getFilterConditions( $sessionFilterKeyPrefix, $additionalConditions = array() ){
		$session	= $this->env->getSession();
		$query		= $session->get( $sessionFilterKeyPrefix.'query' );
		$types		= $session->get( $sessionFilterKeyPrefix.'types' );
		$priorities	= $session->get( $sessionFilterKeyPrefix.'priorities' );
		$states		= $session->get( $sessionFilterKeyPrefix.'states' );
		$projects	= $session->get( $sessionFilterKeyPrefix.'projects' );
		$workers	= $session->get( $sessionFilterKeyPrefix.'workers' );
		$conditions	= array();
		if( is_array( $types ) && count( $types ) )
			$conditions['type']	= $types;
		if( is_array( $priorities ) && count( $priorities ) )
			$conditions['priority']	= $priorities;
		if( is_array( $states ) && count( $states ) )
			$conditions['status']	= $states;
		if( is_array( $workers ) && count( $workers ) )
			$conditions['workerId']	= $workers;
		if( strlen( $query ) )
			$conditions['title']	= '%'.str_replace( array( '*', '?' ), '%', $query ).'%';
		if( is_array( $projects ) && count( $projects ) )											//  if filtered by projects
			$conditions['projectId']	= $projects;												//  apply project conditions
		foreach( $additionalConditions as $key => $value )
			$conditions[$key]			= $value;
		return $conditions;
	}

	public function getUserProjects( $userId, $activeOnly = FALSE ){
		if( !$this->useProjects )																	//  projects module not enabled
			return array();																			//  return empty map
		$modelProject	= new Model_Project( $this->env );											//  create projects model
		if( !$this->hasFullAccess() ){																//  normal access
			$conditions		= $activeOnly ? array( 'status' => array( 0, 1, 2 ) ) : array();		//  ...
			return $modelProject->getUserProjects( $userId, $conditions );							//  return user projects
		}
		$userProjects	= array();																	//  otherwise create empty project map
		foreach( $modelProject->getAll( array(), array( 'title' => 'ASC' ) ) as $project )			//  iterate all projects
			$userProjects[$project->projectId]	= $project;											//  add to projects map
		return $userProjects;																		//  return projects map
	}

	public function getUserMissions( $userId, $conditions = array(), $orders = array(), $limits = NULL ){
		$conditions	= array_merge( $this->generalConditions, $conditions );
		$orders		= $orders ? $orders : array( 'dayStart' => 'ASC' );

		if( $this->hasFullAccess() )																//  user has full access
			return $this->modelMission->getAll( $conditions, $orders, $limits );					//  return all missions matched by conditions

		$havings	= array(																		//  additional conditions
			'creatorId = '.(int) $userId,															//  user is creator
			'workerId = '.(int) $userId,															//  or user is worker
		);
		if( $this->useProjects ){																	//  projects module is enabled
			$userProjects	= array_keys( $this->getUserProjects( $userId, TRUE ) );				//  get user projects from model
			if( $userProjects )																		//  user has projects
				$havings[]	= 'projectId IN ('.join( ',', $userProjects ).')';						//  add projects condition
			array_unshift( $userProjects, 0 );														//
			if( isset( $conditions['projectId'] ) )													//  projects have been selected
				$userProjects	= array_intersect( $conditions['projectId'], $userProjects );		//  intersect user projectes and selected projects
			$conditions['projectId']	= $userProjects;											//
			if( !$conditions['projectId'] )															//  no projects by filter
				unset( $conditions['projectId'] );													//  do not filter projects then
		}
		return $this->modelMission->getAll(															//  return missions matched by conditions
			$conditions,
			$orders,
			$limits,
			array_diff( $this->modelMission->getColumns(), array( 'content' ) ),					//  all columns except content
			array( 'missionId' ),																	//  HAVING needs grouping
			array( join( ' OR ', $havings ) )														//  combine havings with OR
		);
	}

	public function getVersion( $missionId, $version ){
		return $this->modelVersion->getByIndices( array(
			'missionId'	=> $missionId,
			'version'	=> $version,
		) );
	}

	public function getVersions( $missionId ){
		$orders		= array( 'version' => 'ASC' );
		$versions	= $this->modelVersion->getAllByIndex( 'missionId', $missionId, $orders );
		$modelUser	= new Model_User( $this->env );											//  create projects model
		foreach( $versions as $version )
			$version->user = $modelUser->get( $version->userId );
		return $versions;
	}

	protected function hasFullAccess(){
		return $this->env->getAcl()->hasFullAccess( $this->env->getSession()->get( 'roleId' ) );
	}

	public function noteChange( $type, $missionId, $data, $currentUserId ){
		$model	= new Model_Mission_Change( $this->env );
		if( !$model->count( array( 'missionId' => $missionId ) ) ){
			$model->add( array(
				'missionId'		=> $missionId,
				'userId'		=> $currentUserId,
				'type'			=> $type,
				'data'			=> serialize( $data ),
				'timestamp'		=> time()
			), FALSE );
		}
		else{
			$mission	= (array) $this->modelMission->get( $missionId );
			$lastChange	= $model->getByIndex( 'missionId', $missionId );
			$lastData	= (array) unserialize( $lastChange->data );
			unset( $lastData['modifiedAt'] );
			unset( $mission['modifiedAt'] );
			if( json_encode( $mission ) === json_encode( $lastData ) )
				$model->remove( $lastChange->missionChangeId );
		}
	}

	public function noteVersion( $missionId, $userId, $content ){
		$modelVersion	= new Model_Mission_Version( $this->env );
		$latest	= $modelVersion->getByIndex( 'missionId', $missionId, array( 'version' => 'DESC' ) );
		if( $latest && $latest->content === $content )
			return FALSE;
		return $modelVersion->add( array(
			'missionId'	=> $missionId,
			'userId'	=> $userId,
			'version'	=> $modelVersion->countByIndex( 'missionId', $missionId ) + 1,
			'content'	=> $content,
			'timestamp'	=> time(),
		) );
	}

	public function removeDocument( $documentId ){
		$document	= $this->modelDocument->get( $documentId );
		if( !$document )
			return FALSE;
		$path		= 'contents/documents/missions/';
		@unlink( $path.$document->hashname );
		$this->modelDocument->remove( $documentId );
		return TRUE;
	}

	public function removeMission( $missionId ){
		$this->modelChange->removeByIndex( 'missionId', $missionId );
		$this->modelVersion->removeByIndex( 'missionId', $missionId );
		foreach( $this->modelDocument->getAllByIndex( 'projectId', $projectId ) as $document )
			$this->removeDocument( $document->missionDocumentId );
		$this->modelMission->remove( $missionId );
	}

}
?>
