<?php
class Controller_Manage_Project extends CMF_Hydrogen_Controller{

	protected $logic;
	protected $useMissions	= FALSE;
	protected $useCompanies	= FALSE;
	protected $useCustomers	= FALSE;

	public function __onInit(){
		$this->logic		= new Logic_Project( $this->env );
		$this->useMissions	= $this->env->getModules()->has( 'Work_Missions' );
		$this->useCompanies	= $this->env->getModules()->has( 'Manage_Projects_Companies' );
		$this->useCustomers	= $this->env->getModules()->has( 'Manage_Customers' );
		$session			= $this->env->getSession();
		if( !$session->get( 'filter_manage_project_limit' ) )
			$session->set( 'filter_manage_project_limit', 10 );
	}

	static public function ___onUpdate( $env, $module, $context, $data ){
		if( empty( $data['projectId'] ) )
			throw new InvalidArgumentException( 'Missing project ID' );
		$model		= new Model_Project( $env );
		$model->edit( $data['projectId'], array( 'modifiedAt' => time() ) );
	}

	public function add(){
		$request		= $this->env->getRequest();
		$session		= $this->env->getSession();
		$messenger		= $this->env->getMessenger();
		$modelProject	= new Model_Project( $this->env );
		$words			= (object) $this->getWords( 'add' );
		$userId			= $this->env->getSession()->get( 'userId' );

		if( $request->has( 'save') ){
			$title		= $request->get( 'title' );
			if( !strlen( $title ) )
				$messenger->noteError( $words->msgTitleMissing );
			if( $modelProject->count( array( 'title' => $title ) ) )
				$messenger->noteError( $words->msgTitleExisting, $title );
			if( $messenger->gotError() )
				return;

			$data				= $request->getAll();
			$data['createdAt']	= time();
			$projectId			= $modelProject->add( $data, FALSE );

			if( 1 || !$this->env->getAcl()->hasFullAccess( $session->get( 'roleId' ) ) ){
				$modelRelation		= new Model_Project_User( $this->env );
				$modelRelation->add( array(
					'projectId'		=> $projectId,
					'userId'		=> $userId,
				) );
			}
			$messenger->noteSuccess( $words->msgSuccess );
			$this->restart( './manage/project/edit/'.$projectId );
		}
//		$this->addData( 'filterStatus', $session->get( 'filter_manage_project_status' ) );
//		$this->addData( 'filterOrder', $session->get( 'filter_manage_project_order' ) );
//		$this->addData( 'filterDirection', $session->get( 'filter_manage_project_direction' ) );
	}

	public function addUser( $projectId ){
		$userId		= $this->env->getRequest()->get( 'userId' );
		$forwardTo	= $this->env->getRequest()->get( 'forwardTo' );
		if( (int) $userId > 0 ){
			$model		= new Model_Project_User( $this->env );
			$model->add( array(
				'projectId'		=> $projectId,
				'userId'		=> $userId,
				'createdAt'		=> time()
			) );
			if( $forwardTo )
				$this->restart( './'.$forwardTo );
		}
		$this->restart( 'edit/'.$projectId, TRUE );
	}

	public function edit( $projectId ){
		$request		= $this->env->getRequest();
		$session		= $this->env->getSession();
		$messenger		= $this->env->getMessenger();
		$modelProject	= new Model_Project( $this->env );
		$words			= (object) $this->getWords( 'edit' );
		$project		= $modelProject->get( $projectId );
		if( !$project ){
			$this->env->getMessenger()->noteError( $words->msgInvalidId );
			$this->restart( NULL, TRUE );
		}
		if( $request->has( 'save') ){
			$title		= $request->get( 'title' );
			if( !strlen( $title ) )
				$messenger->noteError( $words->msgTitleMissing );
			$found	= $modelProject->getByIndex( 'title', $title );
			if( $found && $found->projectId != $projectId )
				$messenger->noteError( $words->msgTitleExisting, $title );
			if( $messenger->gotError() )
				return;
			$data				= $request->getAll();
			$data['modifiedAt']	= time();
			$modelProject->edit( $projectId, $data , FALSE );
			$messenger->noteSuccess( $words->msgSuccess );
			$this->restart( './manage/project/edit/'.$projectId );
		}

		$model		= new Model_Project_User( $this->env );
		$modelUser	= new Model_User( $this->env );
		$relations	= $model->getAllByIndex( 'projectId', $projectId );

		$users		= array();
		$conditions	= array( 'status' => '>0' );
		$orders		= array( 'username' => 'ASC' );
		foreach( $modelUser->getAll( $conditions, $orders ) as $user )
			$users[$user->userId]	= $user;

		$projectUsers	= array();
		foreach( $relations as $relation ){
			if( empty( $users[$relation->userId] ) )
				$model->removeByIndices( array( 'projectId' => $projectId, 'userId' => $relation->userId ) );
			else
				$projectUsers[$relation->userId]	= $users[$relation->userId];
		}

		if( $this->env->getModules()->has( 'Work_Missions' ) ){
			$modelMission	= new Model_Mission( $this->env );
			$missions		= $modelMission->getAllByIndex( 'projectId', $projectId );
			$this->addData( 'missions', $missions );
		}

		$this->addData( 'users', $users );
		$this->addData( 'project', $project );
		$this->addData( 'projectUsers', $projectUsers );
		$this->addData( 'canEdit', $this->env->getAcl()->has( 'manage_project', 'edit' ) );
		$this->addData( 'canRemove', $this->env->getAcl()->has( 'manage_project', 'remove' ) );
		if( $this->useCompanies ){
			$modelCompany		= new Model_Company( $this->env );
			$modelProjectCompany	= new Model_Project_Company( $this->env );
			$this->addData( 'companies', $modelCompanies->getAll() );				//   @todo: order!
			$conditions		= array( 'projectId' => $project->projectId );
			$this->addData( 'projectCompanies', $modelProjectCompanies->get( $conditions ) );	//   @todo: order!
		}
		if( $this->useCustomers ){
			$modelCustomer	= new Model_Customer( $this->env );
			$modelCustomer->getAll( array( 'userId' => $session->get( 'userId' ) ), array( 'title' => 'ASC' ) );
		}
//		$this->addData( 'filterStatus', $session->get( 'filter_manage_project_status' ) );
//		$this->addData( 'filterOrder', $session->get( 'filter_manage_project_order' ) );
//		$this->addData( 'filterDirection', $session->get( 'filter_manage_project_direction' ) );
	}

	public function filter( $mode = NULL ){
		$request		= $this->env->getRequest();
		$session		= $this->env->getSession();
		if( $mode === "reset" )
			foreach( array_keys( $session->getAll( 'filter_manage_project_' ) ) as $key )
				$session->remove( 'filter_manage_project_'.$key );
		if( $request->has( 'id' ) )
			$session->set( 'filter_manage_project_id', $request->get( 'id' ) );
		if( $request->has( 'query' ) )
			$session->set( 'filter_manage_project_query', $request->get( 'query' ) );
		if( $request->has( 'status' ) )
			$session->set( 'filter_manage_project_status', $request->get( 'status' ) );
		if( $request->has( 'priority' ) )
			$session->set( 'filter_manage_project_priority', $request->get( 'priority' ) );
		if( $request->has( 'user' ) )
			$session->set( 'filter_manage_project_user', $request->get( 'user' ) );
		if( $request->has( 'order' ) )
			$session->set( 'filter_manage_project_order', $request->get( 'order' ) );

		if( $request->has( 'direction' ) )
			$session->set( 'filter_manage_project_direction', $request->get( 'direction' ) );
		if( $request->has( 'limit' ) )
			$session->set( 'filter_manage_project_limit', max( 1, $request->get( 'limit' ) ) );
		if( $session->get( 'filter_manage_project_order' ) === NULL )
			$session->set( 'filter_manage_project_order', 'title' );
		if( $session->get( 'filter_manage_project_direction' ) === NULL )
			$session->set( 'filter_manage_project_direction', 'ASC' );
		$session->set( 'filter_manage_project_page', 0 );
		$this->restart( NULL, TRUE );
	}

	protected function getWorkersOfMyProjects(){
		$session			= $this->env->getSession();
		return $this->logic->getCoworkers( $session->get( 'userId' ) );
	}

	public function index( $page = NULL ){
		$session		= $this->env->getSession();

//		$this->env->getCaptain()->callHook( 'Project', 'update', $this, array( 'projectId' => '43' ) );

		if( $page !== NULL ){																	//  page set as argument
			$session->set( 'filter_manage_project_page', $page );								//  store page in session (will be validated later)
			if( $page === "0" )																	//  page was set to 0 explicitly
				$this->restart( NULL, TRUE );													//  redirect to nicer URI
		}
		else{																					//  no page as argument
			if( preg_match( "@manage/project/[0-9]+$@", getEnv( 'HTTP_REFERER' ) ) )			//  last request was index, too
				$session->set( 'filter_manage_project_page', 0 );								//  assume first page and store in session
			$page	= (int) $session->get( 'filter_manage_project_page' );						//  get page from session
		}
		$modelProject		= new Model_Project( $this->env );
		$modelProjectUser	= new Model_Project_User( $this->env );
		$modelUser			= new Model_User( $this->env );
		if( $this->useMissions )
			$modelMission	= new Model_Mission( $this->env );

		$filterId			= $session->get( 'filter_manage_project_id' );
		$filterQuery		= $session->get( 'filter_manage_project_query' );
		$filterStatus		= $session->get( 'filter_manage_project_status' );
		$filterPriority		= $session->get( 'filter_manage_project_priority' );
		$filterUser			= $session->get( 'filter_manage_project_user' );
		$filterOrder		= $session->get( 'filter_manage_project_order' );
		$filterDirection	= $session->get( 'filter_manage_project_direction' );
		$filterLimit		= $session->get( 'filter_manage_project_limit' );
		if( !is_array( $filterStatus ) )
			$filterStatus	= array();
		if( !is_array( $filterPriority ) )
			$filterPriority	= array();
		if( !is_array( $filterUser ) )
			$filterUser		= array();

		$conditions	= array();
		if( !$this->env->getAcl()->hasFullAccess( $session->get( 'roleId' ) ) ){
			$projects	= array();
			foreach( $modelProjectUser->getAllByIndex( 'userId', $session->get( 'userId' ) ) as $relation )
				$projects[$relation->projectId]	= NULL;
			$conditions['projectId']	= array_keys( $projects );
		}

		if( (int) $filterId > 0 )
			$conditions['projectId']	= array( $filterId );
		else{
			if( strlen( trim( $filterQuery ) ) ){
				$projectIds		= array();
				$filters	= array(
					"title LIKE '%".$filterQuery."%'",
					"description LIKE '%".$filterQuery."%'",
				);
				$query	= "SELECT * FROM ".$modelProject->getName()." WHERE ".join( " OR ", $filters )." LIMIT 1000";
				foreach( $this->env->getDatabase()->query( $query ) as $result )
					$projectIds[]	= $result['projectId'];
				if( isset( $conditions['projectId'] ) )
					$conditions['projectId']	= array_intersect( $conditions['projectId'], $projectIds );
				else
					$conditions['projectId']	= $projectIds;
			}

			if( $filterUser ){
				$projectIds	= array();
				foreach( $modelProjectUser->getAll( array( 'userId' => $filterUser ) ) as $relation )
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
			unset( $conditions['projectId'] );

		$orders	= array();
		if( !( $filterOrder && $filterDirection ) ){
			$filterOrder		= "title";
			$filterDirection	= "ASC";
		}
		$orders[$filterOrder]	= $filterDirection;

		$total	= $modelProject->count( $conditions );
		if( $page * $filterLimit > $total )
			$this->restart( '0', TRUE );
//		$page	= max( 0, min( floor( $total / $filterLimit ), $page ) );
		$limit	= $session->get( 'filter_manage_project_limit' );
		$limits	= array( $page * $filterLimit, $filterLimit );

		$projects	= array();
		foreach( $modelProject->getAll( $conditions, $orders, $limits ) as $project ){
			$projects[$project->projectId]	= $project;
			$project->users	= $modelProjectUser->getAllByIndex( 'projectId', $project->projectId );
			foreach( $project->users as $nr => $projectUser )
				$project->users[$nr]	= $modelUser->get( $projectUser->userId );
			if( $this->useMissions )
				$project->missions	= $modelMission->countByIndex( 'projectId', $project->projectId );
		}
		$this->addData( 'page', $page );
		$this->addData( 'total', $total );
		$this->addData( 'projects', $projects );
		$this->addData( 'users', $this->logic->getCoworkers( $session->get( 'userId' ) ) );
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

	public function removeUser( $projectId, $userId ){
		$modelUser		= new Model_Project_User( $this->env );
		$indices		= array(
			'projectId'		=> $projectId,
			'userId'		=> $userId
		);
		$modelUser->removeByIndices( $indices );
		$this->restart( './manage/project/edit/'.$projectId );
	}

#	public function remove( $projectId ){
#		$model	= new Model_Project( $this->env );
#		$model->remove( $projectId )
#	}
}
?>
