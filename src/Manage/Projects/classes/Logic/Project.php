<?php

use CeusMedia\HydrogenFramework\Logic;

class Logic_Project extends Logic
{
	protected Model_Project $modelProject;
	protected Model_Project_User $modelProjectUser;
	protected Model_User $modelUser;

	public function countProjects(array $conditions = [] ): int
	{
		return $this->modelProject->count( $conditions );
	}

	/**
	 *	@param		int|string		$projectId
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function get( int|string $projectId ): ?object
	{
		return $this->getProject( $projectId );
	}

	/**
	 *	Returns map of related users of a given user ID.
	 *	Either related to a given project ID (project members) or related by other modules (calling a hook).
	 *	Returned map will not contain the user itself.
	 *
	 *	@access		public
	 *	@param		int|string			$userId			User ID to get coworkers for
	 *	@param		int|string|NULL		$projectId		Project ID to get coworkers of
	 *	@param		boolean				$includeSelf	Flag: include or remove given user ID
	 *	@return		array				Map of related users
	 *	@throws		RuntimeException			if user is neither in project nor has full access
	 *	@throws		ReflectionException
	 */
	public function getCoworkers( int|string $userId, int|string|NULL $projectId = NULL, bool $includeSelf = FALSE ): array
	{
		if( 0 == (int) $userId )
			return [];
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

	public function getDefaultProject( int|string $userId )
	{
		$relation	= $this->modelProjectUser->getByIndices( [
			'userId'	=> $userId,
			'isDefault'	=> 1
		] );
		return $relation ? $relation->projectId : NULL;
	}

	/**
	 *	@param		int|string		$projectId
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getProject( int|string $projectId ): ?object
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
	 *	@param		int|string		$projectId		Project ID
	 *	@param		array			$conditions		Map of conditions for users to follow
	 *	@param		array			$orders			Map how to order users, defaults to 'username ASC'
	 *	@return		array			Map of users assigned to project
	 */
	public function getProjectUsers( int|string $projectId, array $conditions = [], array $orders = [] ): array
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
		return $this->modelProject->getProjectsUsers( $projectIds, $conditions, $orders );
	}

	/**
	 *	Returns projects where a user (by its ID) is assigned to.
	 *	@access		public
	 *	@param		int|string		$userId			User ID
	 *	@param		boolean			$activeOnly		Flag: List only active projects
	 *	@param		array			$conditions		Map of conditions for projects to follow
	 *	@param		array			$orders			Map how to order projects, defaults to 'title ASC'
	 *	@return		array			List of projects of user
	 */
	public function getUserProjects( int|string $userId, bool $activeOnly = FALSE, array $conditions = [], array $orders = [] ): array
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
	 *	@param		int|string		$userIds		List of user IDs
	 *	@param		boolean			$activeOnly		Flag: List only active projects
	 *	@param		array			$conditions		Map of conditions for projects to follow
	 *	@param		array			$orders			Map how to order projects, defaults to 'title ASC'
	 *	@return		array			List of projects of user
	 */
	public function getUsersProjects( int|string $userIds, bool $activeOnly = FALSE, array $conditions = [], array $orders = [] ): array
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
	 *	@param		object				$project
	 *	@param		int|string|NULL		$excludeUserId
	 *	@return		int
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function informMembersAboutChange( object $project, int|string|NULL $excludeUserId = 0 ): int
	{
		$mailClassName	= Mail_Manage_Project_Changed::class;
		return $this->sendMailToMembersByClassName( $mailClassName, $project, $excludeUserId );
	}

	/**
	 *	@param		object				$project
	 *	@param		int|string|NULL		$excludeUserId
	 *	@return		int
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function informMembersAboutMembers( object $project, int|string|NULL $excludeUserId = 0 ): int
	{
		$mailClassName	= Mail_Manage_Project_Members::class;
		return $this->sendMailToMembersByClassName( $mailClassName, $project, $excludeUserId );
	}

	/**
	 *	@param		object				$project
	 *	@param		int|string|NULL		$excludeUserId
	 *	@return		int
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function informMembersAboutRemoval( object $project, int|string|NULL $excludeUserId = 0 ): int
	{
		$mailClassName	= Mail_Manage_Project_Removed::class;
		return $this->sendMailToMembersByClassName( $mailClassName, $project, $excludeUserId ?? 0 );
	}

	/**
	 *	@param		int|string		$projectId
	 *	@param		int|string		$userId
	 *	@param		bool			$informOthers
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeProjectUser( int|string $projectId, int|string $userId, bool $informOthers = TRUE ): void
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
						/** @var ?Entity_User $user */
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
	 *	@param		int|string		$userId
	 *	@param		int|string		$projectId
	 *	@return		int
	 */
	public function setDefaultProject( int|string $userId, int|string $projectId ): int
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

	/**
	 *	@param		string			$mailClassName
	 *	@param		object			$project
	 *	@param		int|string		$excludeUserId
	 *	@return		bool
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function sendMailToMembersByClassName( string $mailClassName, object $project, int|string $excludeUserId ): bool
	{
		if( !$this->env->getModules()->has( 'Resource_Mail' ) )
			return 0;
		$language		= $this->env->getLanguage();											//  get language support
		$counter	= 0;
		foreach( $this->getProjectUsers( $project->projectId ) as $member ){					//  iterate project users
			if( $member->userId == $excludeUserId )												//  project user is current user
				continue;																		//  skip
			$data	= ['project' => $project, 'user' => $member];
			$mail	= new $mailClassName( $this->env, $data, FALSE );
			/** @var Logic_Mail $logicMail */
			$logicMail	= Logic_Mail::getInstance( $this->env );
			$counter	+= (int) $logicMail->handleMail( $mail, $member, $language->getLanguage() );
		}
		return $counter;
	}
}
