<?php
class Logic_Project extends CMF_Hydrogen_Environment_Resource_Logic{

	protected $model;

	public function __construct( $env ){
		parent::__construct( $env );
		$this->modelProject		= new Model_Project( $this->env );                                          //  create projects model
		$this->modelProjectUser	= new Model_Project_User( $this->env );
	}

	protected function hasFullAccess(){
		return $this->env->getAcl()->hasFullAccess( $this->env->getSession()->get( 'roleId' ) );
	}

	public function get( $projectId ){
		return $this->getProject( $projectId );
	}

	public function getCoworkers( $userId, $projectId = 0 ){
		if( $projectId ){
			$users	= $this->getProjectUsers( $projectId );
			if( !isset( $users[$userId] ) )
				throw new RuntimeException( 'User with ID '.$userId.' is not member of project with ID '.$projectId );
			unset( $users[$userId] );
			return $users;
		}
		return Logic_Authentication::getInstance( $this->env )->getRelatedUsers( $userId );
	}

	public function getDefaultProject( $userId ){
		$relation	= $this->modelProjectUser->getByIndices( array(
			'userId'	=> $userId,
			'isDefault'	=> 1
		) );
		return $relation ? $relation->projectId : NULL;
	}

	public function getProject( $projectId ){
		return $this->modelProject->get( $projectId );
	}

	public function getProjects( $conditions = array(), $orders = array(), $limits = array() ){
		$projects	= $this->modelProject->getAll( $conditions, $orders, $limits );
//		@todo   	replace the last 3 lines below by this more performant code after testing
//		$projectIds	= array();
//		foreach( $projects as $project )
//			$projectIds[]	= $project->projectId;
		foreach( $projects as $project )
			$project->user	= $this->getProjectUsers( $project->projectId );
		return $projects;
	}

	/**
	 *	Returns list of users assigned to a project.
	 *	@access		public
	 *	@param		integer		$projectId		Project ID
	 *	@param		array       $conditions     Map of conditions for users to follow
	 *	@param		array       $orders         Map how to order users, defaults to 'username ASC'
	 *	@return		array		Map of users assigned to project
	 */
	public function getProjectUsers( $projectId, $conditions = array(), $orders = array() ){
		return $this->modelProject->getProjectUsers( $projectId, $conditions, $orders );
	}

	/**
	 *	Returns list of users assigned to projects.
	 *	@access		public
	 *	@param		array		$projectIds		List of Project IDs
	 *	@param		array       $conditions     Map of conditions for users to follow
	 *	@param		array       $orders         Map how to order users, defaults to 'username ASC'
	 *	@return		array		Map of users assigned to project
	 */
	public function getProjectsUsers( $projectIds, $conditions = array(), $orders = array() ){
		return $this->modelProject->getProjectUsers( $projectIds, $conditions, $orders );
	}

	/**
	 *	Returns projects where a user (by its ID) is assigned to.
	 *	@access		public
	 *	@param		integer		$userId			User ID
	 *	@param		boolean		$activeOnly		Flag: List only active projects
	 *	@param		array		$conditions		Map of conditions for projects to follow
	 *	@param		array		$orders			Map how to order projects, defaults to 'title ASC'
	 *	@return		array		List of projects of user
	 */
	public function getUserProjects( $userId, $activeOnly = FALSE, $conditions = array(), $orders = array() ){
		$orders			= $orders ? $orders : array( 'title' => 'ASC' );							//  sanitize project orders
		$userProjects	= array();																	//  create empty project map
		if( $this->hasFullAccess() ){																//  super access
			foreach( $this->modelProject->getAll( $conditions, $orders ) as $project )				//  iterate all projects
				$userProjects[$project->projectId]  = $project;										//  add to projects map
		}
		else{																						//  normal access
			if( $activeOnly )																		//  reduce to active projects
				$conditions['status']	= array( 0, 1, 2 );											//  @todo kriss: insert Model_Project::STATES_ACTIVE of available
			$projects	= $this->modelProject->getUserProjects( $userId, $conditions, $orders );
			foreach( $projects as $project )														//  get and iterate user assigned projects
				$userProjects[$project->projectId]  = $project;										//  add to projects map
		}
		return $userProjects;																		//  return projects map
	}
	/**
	 *	Returns projects where users (by their ID) are assigned to.
	 *	@access		public
	 *	@param		array		$userIds		List of user IDs
	 *	@param		boolean		$activeOnly		Flag: List only active projects
	 *	@param		array		$conditions		Map of conditions for projects to follow
	 *	@param		array		$orders			Map how to order projects, defaults to 'title ASC'
	 *	@return		array		List of projects of user
	 */
	public function getUsersProjects( $userIds, $activeOnly = FALSE, $conditions = array(), $orders = array() ){
		$orders			= $orders ? $orders : array( 'title' => 'ASC' );							//  sanitize project orders
		$userProjects	= array();																	//  create empty project map
		if( $this->hasFullAccess() ){																//  super access
			foreach( $this->modelProject->getAll( $conditions, $orders ) as $project )				//  iterate all projects
				$userProjects[$project->projectId]  = $project;										//  add to projects map
		}
		else{																						//  normal access
			if( $activeOnly )																		//  reduce to active projects
				$conditions['status']	= array( 0, 1, 2 );											//  @todo kriss: insert Model_Project::STATES_ACTIVE of available
			$projects	= $this->modelProject->getUserProjects( $userIds, $conditions, $orders );
			foreach( $projects as $project )														//  get and iterate user assigned projects
				$userProjects[$project->projectId]  = $project;										//  add to projects map
		}
		return $userProjects;																		//  return projects map
	}

	public function setDefaultProject( $userId, $projectId ){
		$this->modelProjectUser->editByIndices( array(
			'userId'		=> $userId,
			'isDefault'		=> 1
		), array(
			'isDefault'		=> "0",
			'modifiedAt'	=> time()
		) );
		return $this->modelProjectUser->editByIndices( array(
			'projectId'		=> $projectId,
			'userId'		=> $userId,
		), array(
			'isDefault'		=> "1",
			'modifiedAt'	=> time()
		) );
	}
}
?>
