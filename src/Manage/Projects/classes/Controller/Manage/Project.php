<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_Project extends Controller
{
	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected Dictionary $session;
	protected Logic_Project $logic;
	protected Model_Project $modelProject;
	protected Model_Project_User $modelProjectUser;
	protected Model_User $modelUser;
	protected bool $useMissions			= FALSE;
	protected bool $useCompanies		= FALSE;
	protected bool $useCustomers		= FALSE;
	protected int|string $userId		= 0;
	protected int|string $roleId		= 0;
	protected bool $isAdmin;
	protected bool $isEditor;

	/**
	 *	@param		int|string		$projectId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function acceptInvite( int|string $projectId ): void
	{
		$indices	= [
			'projectId'	=> $projectId,
			'userId'	=> $this->userId,
			'status'	=> 0,
		];
		$relation	= $this->modelProjectUser->getByIndices( $indices );
		if( !$relation ){
			$this->messenger->noteError( 'Keine Einladung zu diesem Projekt vorhanden.' );
		}
		else{
			$this->modelProjectUser->edit( $relation->projectUserId, [
				'status'		=> 1,
				'modifiedAt'	=> time(),
			] );
			$this->messenger->noteSuccess( 'Die Einladung wurde zu einer Mitgliedschaft am Projekt umgewandelt.' );
		}
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		$words			= (object) $this->getWords( 'add' );

		$this->addData( 'from', $this->request->get( 'from' ) );
		if( $this->request->has( 'save') ){
			$title		= $this->request->get( 'title' );
			if( !strlen( $title ) )
				$this->messenger->noteError( $words->msgTitleMissing );
			if( $this->logic->countProjects( ['title' => $title, 'creatorId' => $this->userId] ) )
				$this->messenger->noteError( $words->msgTitleExisting, $title );
			if( !$this->messenger->gotError() ){
				$isFirstUserProject	= !$this->logic->countProjects( ['creatorId' => $this->userId] );

				$data				= $this->request->getAll();
				$data['creatorId']	= $this->userId;
				$data['createdAt']	= time();
				$data['modifiedAt']	= time();

				try{
					$this->env->getDatabase()->beginTransaction();
					$projectId			= $this->modelProject->add( $data, FALSE );
					$this->modelProjectUser->add( [
						'projectId'		=> $projectId,
						'userId'		=> $this->userId,
						'isDefault'		=> $isFirstUserProject ? 1 : 0,
						'createdAt'		=> time(),
						'modifiedAt'	=> time(),
					] );
					$this->env->getDatabase()->commit();
					$this->messenger->noteSuccess( $words->msgSuccess );
					if( $this->request->get( 'from' ) ){
						$this->messenger->noteNotice( 'Weiterleitung zurück zum Ausgangspunkt.' );
						$this->restart( $this->request->get( 'from' ) );
					}
					$this->restart( 'edit/'.$projectId, TRUE );
				}
				catch( Exception $e ){
					$this->env->getDatabase()->rollBack();
					$this->messenger->noteFailure( $words->msgFailureException );
					$this->restart( NULL, TRUE );
				}
			}
		}
//		$this->addData( 'filterStatus', $this->session->get( 'filter_manage_project_status' ) );
//		$this->addData( 'filterOrder', $this->session->get( 'filter_manage_project_order' ) );
//		$this->addData( 'filterDirection', $this->session->get( 'filter_manage_project_direction' ) );
	}

	/**
	 *	@param		int|string			$projectId
	 *	@param		int|string|NULL		$userId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function addUser( int|string $projectId, int|string|NULL $userId = NULL ): void
	{
		$userId			= $userId ?: $this->request->get( 'userId' );
		$forwardTo		= $this->request->get( 'forwardTo' );
		$words			= (object) $this->getWords( 'edit-panel-users' );
		$project		= $this->modelProject->get( (int) $projectId );
		if( !$project ){
			$this->messenger->noteError( $words->msgInvalidProject );
		}
		else if( (int) $userId > 0 ){
			/** @var ?Entity_User $user */
			$user		= $this->modelUser->get( $userId );
			if( !$user ){
				$this->messenger->noteError( $words->msgInvalidUser );
			}
			else{
				$this->modelProjectUser->add( [
					'projectId'		=> (int) $projectId,
					'creatorId'		=> $this->userId,
					'userId'		=> (int) $userId,
					'status'		=> 1,
					'createdAt'		=> time(),
					'modifiedAt'	=> time(),
				] );
				$this->messenger->noteSuccess( $words->msgUserAdded, $user->username, $project->title );

				$language		= $this->env->getLanguage();
				$this->logic->informMembersAboutMembers( $project, $this->userId );

				if( $forwardTo )
					$this->restart( './'.$forwardTo );
			}
		}
		$this->restart( 'edit/'.$projectId, TRUE );
	}

	/**
	 *	@param		string		$projectId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function declineInvite( string $projectId ): void
	{
		$indices	= [
			'projectId'	=> $projectId,
			'userId'	=> $this->userId,
			'status'	=> 0,
		];
		$relation	= $this->modelProjectUser->getByIndices( $indices );
		if( !$relation ){
			$this->messenger->noteError( 'Keine Einladung zu diesem Projekt vorhanden.' );
		}
		else{
			$this->modelProjectUser->edit( $relation->projectUserId, [
				'status'		=> -1,
				'modifiedAt'	=> time(),
			] );
			$this->messenger->noteSuccess( 'Die Einladung zum Projekt wurde abgelehnt.' );
		}
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		string		$projectId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( string $projectId ): void
	{
		$words			= (object) $this->getWords( 'edit' );
		$project		= $this->checkProject( $projectId );

		if( !( $this->isAdmin || $this->isEditor ) ){
			$this->messenger->noteError( $words->msgNoRightToEdit );
			$this->restart( NULL, TRUE );
		}

		$this->checkDefault();
		if( !$project ){
			$this->messenger->noteError( $words->msgInvalidId );
			$this->restart( NULL, TRUE );
		}
		if( $this->request->has( 'save' ) ){
			$title		= $this->request->get( 'title' );
			if( !strlen( $title ) ){
				$this->messenger->noteError( $words->msgTitleMissing );
				$this->restart( 'edit/'.$projectId, TRUE );
			}
			$found	= $this->logic->getProjects( [
				'title'		=> $title,
				'creatorId'	=> $this->userId,
				'projectId' => '!= '.$projectId,
			] );
			if( [] !== $found ){
				$this->messenger->noteError( $words->msgTitleExisting, $title );
				$this->restart( 'edit/'.$projectId, TRUE );
			}
			$data				= $this->request->getAll();
			$data['modifiedAt']	= time();
			$this->modelProject->edit( $projectId, $data, FALSE );
			$this->messenger->noteSuccess( $words->msgSuccess );
			$this->logic->informMembersAboutChange( $project, $this->userId );

			$this->restart( 'edit/'.$projectId, TRUE );
		}

		$relations		= $this->modelProjectUser->getAllByIndex( 'projectId', $projectId );
		$projectUsers	= $this->logic->getProjectUsers( $projectId );
		$isDefault		= $this->logic->getDefaultProject( $this->userId ) === $projectId;

		if( $this->env->getModules()->has( 'Work_Missions' ) ){
			$modelMission	= new Model_Mission( $this->env );
			$missions		= $modelMission->getAllByIndex( 'projectId', $projectId );
			$this->addData( 'missions', $missions );
		}
		$this->addData( 'currentUserId', $this->userId );
		$this->addData( 'users', $this->logic->getCoworkers( $this->userId ) );
		$this->addData( 'project', $project );
		$this->addData( 'projectUsers', $projectUsers );
		$this->addData( 'canEdit', $this->isAdmin || $this->isEditor );
		$this->addData( 'canRemove', $this->env->getAcl()->has( 'manage_project', 'remove' ) );
		$this->addData( 'isDefault', $isDefault );
		if( $this->useCompanies ){
			if( class_exists( 'Model_Company' ) ){
				$modelCompany			= new Model_Company( $this->env );
				$this->addData( 'companies', $modelCompany->getAll() );				//   @todo: order!
			}
			if( class_exists( 'Model_Project_Company' ) ){
				$modelProjectCompany	= new Model_Project_Company( $this->env );
				$conditions		= ['projectId' => $project->projectId];
				$this->addData( 'projectCompanies', $modelProjectCompany->get( $conditions ) );	//   @todo: order!
			}
		}
		if( $this->useCustomers ){
			$modelCustomer	= new Model_Customer( $this->env );
			$modelCustomer->getAll( ['userId' => $this->userId], ['title' => 'ASC'] );
		}
//		$this->addData( 'filterStatus', $this->session->get( 'filter_manage_project_status' ) );
//		$this->addData( 'filterOrder', $this->session->get( 'filter_manage_project_order' ) );
//		$this->addData( 'filterDirection', $this->session->get( 'filter_manage_project_direction' ) );
	}

	public function filter( ?string $mode = NULL ): void
	{
		if( $mode === "reset" )
			foreach( array_keys( $this->session->getAll( 'filter_manage_project_' ) ) as $key )
				$this->session->remove( 'filter_manage_project_'.$key );
//		if( $this->request->has( 'id' ) )
			$this->session->set( 'filter_manage_project_id', $this->request->get( 'id' ) );
//		if( $this->request->has( 'query' ) )
			$this->session->set( 'filter_manage_project_query', $this->request->get( 'query' ) );
//		if( $this->request->has( 'status' ) )
			$this->session->set( 'filter_manage_project_status', $this->request->get( 'status' ) );
//		if( $this->request->has( 'priority' ) )
			$this->session->set( 'filter_manage_project_priority', $this->request->get( 'priority' ) );
//		if( $this->request->has( 'user' ) )
			$this->session->set( 'filter_manage_project_user', $this->request->get( 'user' ) );
//		if( $this->request->has( 'order' ) )
			$this->session->set( 'filter_manage_project_order', $this->request->get( 'order' ) );

		if( $this->request->has( 'direction' ) )
			$this->session->set( 'filter_manage_project_direction', $this->request->get( 'direction' ) );
		if( $this->request->has( 'limit' ) )
			$this->session->set( 'filter_manage_project_limit', max( 1, $this->request->get( 'limit' ) ) );
		if( $this->session->get( 'filter_manage_project_order' ) === NULL )
			$this->session->set( 'filter_manage_project_order', 'title' );
		if( $this->session->get( 'filter_manage_project_direction' ) === NULL )
			$this->session->set( 'filter_manage_project_direction', 'ASC' );
		$this->session->set( 'filter_manage_project_page', 0 );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		int			$page
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function index( int $page = 0 ): void
	{
		$this->checkDefault();
//		$this->env->getCaptain()->callHook( 'Project', 'update', $this, ['projectId' => '43'] );

		$filterId			= $this->session->get( 'filter_manage_project_id' );
		$filterQuery		= $this->session->get( 'filter_manage_project_query', '' );
		$filterStatus		= $this->session->get( 'filter_manage_project_status' );
		$filterPriority		= $this->session->get( 'filter_manage_project_priority' );
		$filterUser			= $this->session->get( 'filter_manage_project_user' );
		$filterOrder		= $this->session->get( 'filter_manage_project_order' );
		$filterDirection	= $this->session->get( 'filter_manage_project_direction' );
		$filterLimit		= $this->session->get( 'filter_manage_project_limit' );
		if( !is_array( $filterStatus ) )
			$filterStatus	= [];
		if( !is_array( $filterPriority ) )
			$filterPriority	= [];
		if( !is_array( $filterUser ) )
			$filterUser		= [];

		$conditions	= [];
		if( !$this->isAdmin ){
			$projects	= [];
			foreach( $this->modelProjectUser->getAllByIndex( 'userId', $this->userId ) as $relation )
				$projects[$relation->projectId]	= NULL;
			$conditions['projectId']	= array_keys( $projects );
		}

		if( (int) $filterId > 0 )
			$conditions['projectId']	= [$filterId];
		else{
			if( 0 !== strlen( trim( $filterQuery ?? '' ) ) ){
				$projectIds		= [];
				$filters	= [
					"title LIKE '%".$filterQuery."%'",
					"description LIKE '%".$filterQuery."%'",
				];
				$query	= "SELECT * FROM ".$this->modelProject->getName()." WHERE ".join( " OR ", $filters )." LIMIT 1000";
				foreach( $this->env->getDatabase()->query( $query ) as $result )
					$projectIds[]	= $result['projectId'];
				if( isset( $conditions['projectId'] ) )
					$conditions['projectId']	= array_intersect( $conditions['projectId'], $projectIds );
				else
					$conditions['projectId']	= $projectIds;
			}
			if( $filterUser ){
				$projectIds	= [];
				foreach( $this->modelProjectUser->getAll( ['userId' => $filterUser] ) as $relation )
					$projectIds[]	= $relation->projectId;
				if( isset( $conditions['projectId'] ) )
					$conditions['projectId']	= array_intersect( $conditions['projectId'], $projectIds );
				else
					$conditions['projectId']	= $projectIds;
			}
		}
		if( $filterStatus )
			$conditions['status']	= $filterStatus;
		if( $filterPriority )
			$conditions['priority']	= $filterPriority;
		if( isset( $conditions['projectId'] ) && !$conditions['projectId'] )
			$conditions['projectId'] = [0];
		$total	= $this->logic->countProjects( $conditions );

		$orders	= [];
		if( !( $filterOrder && $filterDirection ) ){
			$filterOrder		= "title";
			$filterDirection	= "ASC";
		}
		$orders[$filterOrder]	= $filterDirection;

		if( $page * $filterLimit > $total )
			$this->restart( '0', TRUE );
//		$page	= max( 0, min( floor( $total / $filterLimit ), $page ) );
		$limit	= $this->session->get( 'filter_manage_project_limit' );
		$limits	= [$page * $filterLimit, $filterLimit];

		$projects	= [];
		foreach( $this->logic->getProjects( $conditions, $orders, $limits ) as $project ){
			$projects[$project->projectId]	= $project;
			$project->users	= $this->modelProjectUser->getAllByIndex( 'projectId', $project->projectId );
			$project->isDefault	= FALSE;
			foreach( $project->users as $nr => $projectUser ){
				if( $projectUser->userId == $this->userId )
					$project->isDefault	= (bool) $projectUser->isDefault;
				$project->users[$nr]	= $this->modelUser->get( $projectUser->userId );
			}
			if( $this->useMissions ){
				$modelMission	= new Model_Mission( $this->env );
				$project->missions	= $modelMission->countByIndex( 'projectId', $project->projectId );
			}
		}
		$this->addData( 'page', $page );
		$this->addData( 'total', $total );
		$this->addData( 'projects', $projects );
		$this->addData( 'users', $this->logic->getCoworkers( $this->userId ) );
		$this->addData( 'filterId', $filterId );
		$this->addData( 'filterQuery', $filterQuery );
		$this->addData( 'filterStatus', $filterStatus );
		$this->addData( 'filterPriority', $filterPriority );
		$this->addData( 'filterUser', $filterUser );
		$this->addData( 'filterOrder', $filterOrder );
		$this->addData( 'filterDirection', $filterDirection );
		$this->addData( 'filterLimit', $filterLimit );
		$this->addData( 'canAdd', $this->env->getAcl()->has( 'manage_project', 'add' ) );
		$this->addData( 'canFilter', $this->env->getAcl()->has( 'manage_project', 'filter' ) );
		$this->addData( 'canEdit', $this->env->getAcl()->has( 'manage_project', 'edit' ) );
	}

	/**
	 *	@todo		finish: implement hook on other modules and test
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( string $projectId, bool $confirmed = FALSE ): void
	{
		$this->checkDefault();
		$project	= $this->checkProject( $projectId );

		if( $confirmed && $this->request->has( 'remove' ) ){
			$dbc	= $this->env->getDatabase();
			$words	= (object) $this->getWords( 'remove' );
			try{
				$dbc->beginTransaction();
				$payload	= ['projectId' => $projectId];
				$this->env->getCaptain()->callHook( 'Project', 'remove', $this, $payload );
				$dbc->commit();
				$this->messenger->noteSuccess( $words->msgSuccessRemoved, $project->title );
				$this->logic->informMembersAboutRemoval( $project );
				$this->restart( NULL, TRUE );
			}
			catch( Exception $e ){
				$dbc->rollBack();
				$payload	= ['exception' => $e];
				$this->env->getCaptain()->callHook( 'Env', 'logException', $this, $payload );
				$this->messenger->noteFailure( $words->msgFailureException, $e->getMessage() );
				$this->restart( 'edit/'.$projectId, TRUE );
			}
		}
		$this->addData( 'project', $project );
	}

	/**
	 *	@param		string		$projectId
	 *	@param		string		$userId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeUser( string $projectId, string $userId ): void
	{
		$project		= $this->checkProject( $projectId );
		$words			= (object) $this->getWords( 'edit-panel-users' );
		$numberUsers	= 0;																	//  prepare user counter
		$relations		= $this->modelProjectUser->getAllByIndex( 'projectId', $projectId );	//  get project user relations
		$user			= NULL;
		foreach( $relations as $relation ){														//  iterate relations
			/** @var ?Entity_User $relatedUser */
			$relatedUser	= $this->modelUser->get( $relation->userId );						//  get user from relation
			$numberUsers	+= ( $relatedUser && $relatedUser->status > 0 ) ? 1 : 0;			//  count only existing and active users
			if( $relatedUser->userId === $userId )
				$user	= $relatedUser;
		}
		if( $numberUsers < 2 ){
			$this->messenger->noteError( $words->msgCannotRemoveLastUser );
		}
		else if( !$user ){
			$this->messenger->noteError( $words->msgInvalidUser );
		}
		else{
			$this->logic->removeProjectUser( $projectId, $userId );
			$this->messenger->noteSuccess( $words->msgUserRemoved, $user->username, $project->title );
			$this->logic->informMembersAboutMembers( $project, $this->userId );
		}
		if( $userId == $this->userId )
			$this->restart( NULL, TRUE );
		$this->restart( 'edit/'.$projectId, TRUE );
	}

	/**
	 *	@param		string|NULL		$projectId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setDefault( string $projectId = NULL ): void
	{
		$this->checkUserProjects();
		$projectId	= $projectId ?: $this->request->get( 'projectId' );

		$projects	= $this->modelProject->getUserProjects( $this->userId );
		if( count( $projects ) === 1 ){
			$first		= array_slice( $projects, 0, 1 );
			$projectId	= $first[0]->projectId;
		}
		if( $projectId ){
			$this->checkProject( $projectId );
			$this->logic->setDefaultProject( $this->userId, $projectId );
			if( ( $from = $this->request->get( 'from' ) ) )
				$this->restart( $from );
			$this->restart( 'edit/'.$projectId, TRUE );
		}
		$this->addData( 'projects', $projects );
		$this->addData( 'from', $this->request->get( 'from' ) );
	}

	/**
	 *	@param		string		$projectId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function view( string $projectId ): void
	{
		$project			= $this->checkProject( $projectId );
		$project->users		= $this->logic->getProjectUsers( $projectId );
		$project->coworkers	= $this->logic->getCoworkers( $this->userId, $projectId );
		$project->creator	= $project->creatorId ? $project->users[$project->creatorId] : NULL;

		$isOwner		= $project->creatorId == $this->userId;
		$isWorker		= array_key_exists( $this->userId, $project->users );

		$this->addData( 'project', $project );
		$this->addData( 'canEdit', $this->isAdmin || $this->isEditor );
	}

	//  --  PROTECTED  --  //

	/**
	 * @throws ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->messenger		= $this->env->getMessenger();
		$this->useMissions		= $this->env->getModules()->has( 'Work_Missions' );
		$this->useCompanies		= $this->env->getModules()->has( 'Manage_Projects_Companies' );
		$this->useCustomers		= $this->env->getModules()->has( 'Manage_Customers' );
		$this->userId			= $this->session->get( 'auth_user_id', 0 );
		$this->roleId			= $this->session->get( 'auth_role_id', 0 );
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logic			= $this->getLogic( 'Project' );
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->modelProject		= $this->getModel( 'Project' );
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->modelProjectUser	= $this->getModel( 'Project_User' );
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->modelUser		= $this->getModel( 'User' );
		$this->isAdmin			= $this->env->getAcl()->hasFullAccess( $this->roleId ?? '' );
		$this->isEditor			= $this->env->getAcl()->has( 'manage_project', 'edit' );

		if( !$this->session->get( 'filter_manage_project_limit' ) )
			$this->session->set( 'filter_manage_project_limit', 15 );
	}

	protected function checkDefault(): void
	{
		$default	= $this->modelProjectUser->getByIndices( [
			'userId'	=> $this->userId,
			'isDefault'	=> 1,
		] );
		if( !$default ){
			$from	= $this->request->get( '__path' );
			$this->restart( 'setDefault'.( $from ? '?from='.$from : '' ), TRUE );
		}
	}

	/**
	 *	@param		string		$projectId
	 *	@param		bool		$checkMembership
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function checkProject( string $projectId, bool $checkMembership = TRUE ): ?object
	{
		$project		= $this->modelProject->get( $projectId );
		if( !$project ){
			$this->messenger->noteError( 'Invalid project. Redirection to index.' );
			$this->restart( NULL, TRUE );
		}
		if( $checkMembership ){
			$isMember	= $this->modelProjectUser->getByIndices( [
				'projectId'	=> $projectId,
				'userId'	=> $this->userId,
			] );
			if( !$isMember && !$this->isAdmin ){
				$this->messenger->noteError( 'You cannot access this project. Redirection to index.' );
				$this->restart( NULL, TRUE );
			}
		}
		return $project;
	}

	protected function checkUserProjects(): void
	{
		if( !$this->modelProjectUser->countByIndex( 'userId', $this->userId ) ){
			$words		= (object) $this->getWords( 'index' );
			$this->messenger->noteNotice( $words->msgErrorNoProjects );
			$this->restart( 'add', TRUE );
		}
	}

	/**
	 *	@return		array
	 *	@throws		ReflectionException
	 */
	protected function getWorkersOfMyProjects(): array
	{
		return $this->logic->getCoworkers( $this->userId );
	}
}
