<?php
class Controller_Manage_Project extends CMF_Hydrogen_Controller{

	protected $useMissions	= FALSE;
	protected $useCompanies	= FALSE;
	protected $useCustomers	= FALSE;

	public function __onInit(){
		$this->useMissions	= $this->env->getModules()->has( 'Work_Missions' );
		$this->useCompanies	= $this->env->getModules()->has( 'Manage_Projects_Companies' );
		$this->useCustomers	= $this->env->getModules()->has( 'Manage_Customers' );
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
		$this->addData( 'filterStatus', $session->get( 'filter_manage_project_status' ) );
		$this->addData( 'filterOrder', $session->get( 'filter_manage_project_order' ) );
		$this->addData( 'filterDirection', $session->get( 'filter_manage_project_direction' ) );
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
		$this->addData( 'filterStatus', $session->get( 'filter_manage_project_status' ) );
		$this->addData( 'filterOrder', $session->get( 'filter_manage_project_order' ) );
		$this->addData( 'filterDirection', $session->get( 'filter_manage_project_direction' ) );
	}

	public function filter( $mode = NULL ){
		$request		= $this->env->getRequest();
		$session		= $this->env->getSession();
		if( $mode === "reset" )
			foreach( array_keys( $session->getAll( 'filter_manage_project_' ) ) as $key )
				$session->remove( 'filter_manage_project_'.$key );
		if( $request->has( 'status' ) )
			$session->set( 'filter_manage_project_status', $request->get( 'status' ) );
		if( $request->has( 'order' ) )
			$session->set( 'filter_manage_project_order', $request->get( 'order' ) );

		if( $request->has( 'direction' ) )
			$session->set( 'filter_manage_project_direction', $request->get( 'direction' ) );
		if( $request->has( 'limit' ) )
			$session->set( 'filter_manage_project_limit', $request->get( 'limit' ) );
		if( $session->get( 'filter_manage_project_order' ) === NULL )
			$session->set( 'filter_manage_project_order', 'title' );
		if( $session->get( 'filter_manage_project_direction' ) === NULL )
			$session->set( 'filter_manage_project_direction', 'ASC' );
		$this->restart( NULL, TRUE );
	}

	public function index(){
		$session			= $this->env->getSession();
		$modelProject		= new Model_Project( $this->env );
		$modelProjectUser	= new Model_Project_User( $this->env );
		$modelUser			= new Model_User( $this->env );
		if( $this->useMissions )
			$modelMission	= new Model_Mission( $this->env );

		$filterStatus		= $session->get( 'filter_manage_project_status' );
		$filterOrder		= $session->get( 'filter_manage_project_order' );
		$filterDirection	= $session->get( 'filter_manage_project_direction' );
		if( !is_array( $filterStatus ) )
			$filterStatus	= array();

		$conditions	= array();
		if( !$this->env->getAcl()->hasFullAccess( $session->get( 'roleId' ) ) ){
			$projects	= array( 0 => NULL );
			foreach( $modelProjectUser->getAllByIndex( 'userId', $session->get( 'userId' ) ) as $relation )
				$projects[$relation->projectId]	= NULL;
			$conditions['projectId']	= array_keys( $projects );
		}
		if( $filterStatus )
			$conditions['status']	= $filterStatus;
		$orders	= array();
		if( !( $filterOrder && $filterDirection ) ){
			$filterOrder		= "title";
			$filterDirection	= "ASC";
		}
		$orders[$filterOrder]	= $filterDirection;

		$projects	= array();
		foreach( $modelProject->getAll( $conditions, $orders ) as $project ){
			$projects[$project->projectId]	= $project;
			$project->users	= $modelProjectUser->getAllByIndex( 'projectId', $project->projectId );
			foreach( $project->users as $nr => $projectUser )
				$project->users[$nr]	= $modelUser->get( $projectUser->userId );
			if( $this->useMissions )
				$project->missions	= $modelMission->countByIndex( 'projectId', $project->projectId );
		}


		$this->addData( 'projects', $projects );
		$this->addData( 'filterStatus', $filterStatus );
		$this->addData( 'filterOrder', $filterOrder );
		$this->addData( 'filterDirection', $filterDirection );
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
