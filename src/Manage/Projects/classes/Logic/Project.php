<?php

use CeusMedia\HydrogenFramework\Logic;

class Logic_Project extends Logic
{
	protected Model_Project $modelProject;
	protected Model_Project_User $modelProjectUser;
	protected Model_User $modelUser;

	/**
	 *	@param		string		$projectId
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function get( string $projectId ): ?object
	{
		return $this->getProject( $projectId );
	}

	/**
	 *	Returns map of related users of a given user ID.
	 *	Either related to a given project ID (project members) or related by other modules (calling a hook).
	 *	Returned map will not contain the user itself.
	 *
	 *	@access		public
	 *	@param		string			$userId			User ID to get coworkers for
	 *	@param		string|NULL		$projectId		Project ID to get coworkers of
	 *	@param		boolean			$includeSelf	Flag: include or remove given user ID
	 *	@return		array			Map of related users
	 *	@throws		RuntimeException			if user is neither in project nor has full access
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getCoworkers( string $userId, ?string $projectId = NULL, bool $includeSelf = FALSE ): array
	{
		if( $projectId ){
			$users	= $this->getProjectUsers( $projectId );
			if( !isset( $users[$userId] ) && !$this->hasFullAccess() )
				throw new RuntimeException( 'User with ID '.$userId.' is not member of project with ID '.$projectId );
			if( !$includeSelf )
				unset( $users[$userId] );
			return $users;
		}
		$logicAuth	= Logic_Authentication::getInstance( $this->env );
		$users	= $logicAuth->getRelatedUsers( $userId );
		if( !$includeSelf )
			unset( $users[$userId] );
		return $users;
	}

	public function getDefaultProject( string $userId )
	{
		$relation	= $this->modelProjectUser->getByIndices( [
			'userId'	=> $userId,
			'isDefault'	=> 1
		] );
		return $relation ? $relation->projectId : NULL;
	}

	/**
	 *	@param		string		$projectId
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getProject( string $projectId ): ?object
	{
		return $this->modelProject->get( $projectId );
	}

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limits
	 *	@return		array
	 */
	public function getProjects( array $conditions = [], array $orders = [], array $limits = [] ): array
	{
		$projects	= $this->modelProject->getAll( $conditions, $orders, $limits );
//		@todo   	replace the last 3 lines below by this more performant code after testing
//		$projectIds	= [];
//		foreach( $projects as $project )
//			$projectIds[]	= $project->projectId;
		foreach( $projects as $project )
			$project->user	= $this->getProjectUsers( $project->projectId );
		return $projects;
	}

	/**
	 *	Returns list of users assigned to a project.
	 *	@access		public
	 *	@param		string		$projectId		Project ID
	 *	@param		array		$conditions		Map of conditions for users to follow
	 *	@param		array		$orders			Map how to order users, defaults to 'username ASC'
	 *	@return		array		Map of users assigned to project
	 */
	public function getProjectUsers( string $projectId, array $conditions = [], array $orders = [] ): array
	{
		return $this->modelProject->getProjectUsers( $projectId, $conditions, $orders );
	}

	/**
	 *	Returns list of users assigned to projects.
	 *	@access		public
	 *	@param		array		$projectIds		List of Project IDs
	 *	@param		array		$conditions		Map of conditions for users to follow
	 *	@param		array		$orders			Map how to order users, defaults to 'username ASC'
	 *	@return		array		Map of users assigned to project
	 */
	public function getProjectsUsers( array $projectIds, array $conditions = [], array $orders = [] ): array
	{
		return $this->modelProject->getProjectUsers( $projectIds, $conditions, $orders );
	}

	/**
	 *	Returns projects where a user (by its ID) is assigned to.
	 *	@access		public
	 *	@param		string		$userId			User ID
	 *	@param		boolean		$activeOnly		Flag: List only active projects
	 *	@param		array		$conditions		Map of conditions for projects to follow
	 *	@param		array		$orders			Map how to order projects, defaults to 'title ASC'
	 *	@return		array		List of projects of user
	 */
	public function getUserProjects( string $userId, bool $activeOnly = FALSE, array $conditions = [], array $orders = [] ): array
	{
		$orders			= $orders ?: ['title' => 'ASC'];											//  sanitize project orders
		$userProjects	= [];																		//  create empty project map
		if( $this->hasFullAccess() ){																//  super access
			foreach( $this->modelProject->getAll( $conditions, $orders ) as $project )				//  iterate all projects
				$userProjects[$project->projectId]  = $project;										//  add to projects map
		}
		else{																						//  normal access
			if( $activeOnly )																		//  reduce to active projects
				$conditions['status']	= [0, 1, 2];												//  @todo  insert Model_Project::STATES_ACTIVE of available
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
	public function getUsersProjects( array $userIds, bool $activeOnly = FALSE, array $conditions = [], array $orders = [] ): array
	{
		$orders			= $orders ?: ['title' => 'ASC'];											//  sanitize project orders
		$userProjects	= [];																		//  create empty project map
		if( $this->hasFullAccess() ){																//  super access
			foreach( $this->modelProject->getAll( $conditions, $orders ) as $project )				//  iterate all projects
				$userProjects[$project->projectId]  = $project;										//  add to projects map
		}
		else{																						//  normal access
			if( $activeOnly )																		//  reduce to active projects
				$conditions['status']	= [0, 1, 2];											//  @todo  insert Model_Project::STATES_ACTIVE of available
			$projects	= $this->modelProject->getUserProjects( $userIds, $conditions, $orders );
			foreach( $projects as $project )														//  get and iterate user assigned projects
				$userProjects[$project->projectId]  = $project;										//  add to projects map
		}
		return $userProjects;																		//  return projects map
	}

	/**
	 *	@param		string		$projectId
	 *	@param		string		$userId
	 *	@param		bool		$informOthers
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeProjectUser( string $projectId, string $userId, bool $informOthers = TRUE ): void
	{
		try{
			$this->modelProjectUser->removeByIndices( [
				'projectId'		=> $projectId,
				'userId'		=> $userId
			] );
			if( $informOthers ){
				$logicMail		= Logic_Mail::getInstance( $this->env );
				$language		= $this->env->getLanguage();
				foreach( $this->getProjectUsers( $projectId ) as $member ){
					if( $member->userId !== $userId ){
						$user	= $this->modelUser->get( $member->userId );
						$data	= array(
							'project'	=> $this->getProject( $projectId ),
							'user'		=> $user,
						);
						$mail	= new Mail_Manage_Project_Members( $this->env, $data, FALSE );
						$logicMail->handleMail( $mail, $user, $language->getLanguage() );
					}
				}
			}
		}
		catch( Exception $e ){
			throw new RuntimeException( 'Removing project user failed ('.$e->getMessage().')', 0, $e );
		}
	}

	/**
	 *	@param		string		$userId
	 *	@param		string		$projectId
	 *	@return		int
	 */
	public function setDefaultProject( string $userId, string $projectId ): int
	{
		$this->modelProjectUser->editByIndices( [
			'userId'		=> $userId,
			'isDefault'		=> 1
		], [
			'isDefault'		=> "0",
			'modifiedAt'	=> time()
		] );
		return $this->modelProjectUser->editByIndices( [
			'projectId'		=> $projectId,
			'userId'		=> $userId,
		], [
			'isDefault'		=> "1",
			'modifiedAt'	=> time()
		] );
	}

	//  --  PROTECTED  --  //

	protected function __onInit(): void
	{
		$this->modelProject		= new Model_Project( $this->env );								//  create projects model
		$this->modelProjectUser	= new Model_Project_User( $this->env );
		$this->modelUser		= new Model_User( $this->env );									//  create user model
	}

	protected function hasFullAccess(): bool
	{
		return $this->env->getAcl()->hasFullAccess( $this->env->getSession()->get( 'auth_role_id' ) );
	}
}
