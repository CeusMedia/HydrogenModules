<?php
class Controller_Manage_Project extends CMF_Hydrogen_Controller{

	protected $useMissions	= FALSE;

	public function __onInit(){
		$this->useMissions	= $this->env->getModules()->has( 'Work_Missions' );
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
		foreach( $modelUser->getAll() as $user )
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
	}

	public function filter( $mode = NULL ){
		$request		= $this->env->getRequest();
		$session		= $this->env->getSession();
		foreach( array_keys( $session->getAll( 'filter_manage_project_' ) ) as $key )
			$session->remove( 'filter_manage_project_'.$key );
		if( $mode !== "reset" ){
			$session->set( 'filter_manage_project_status', $request->get( 'status' ) );
			$session->set( 'filter_manage_project_order', $request->get( 'order' ) );
			$session->set( 'filter_manage_project_direction', $request->get( 'direction' ) );
			$session->set( 'filter_manage_project_limit', $request->get( 'limit' ) );
		}
		$this->restart( NULL, TRUE );
	}

	public function index(){
		$session		= $this->env->getSession();
		$modelProject	= new Model_Project( $this->env );
		$modelUser		= new Model_Project_User( $this->env );
		if( $this->useMissions )
			$modelMission	= new Model_Mission( $this->env );
		
		$filterStatus	= $session->get( 'filter_manage_project_status' );
		if( !is_array( $filterStatus ) )
			$filterStatus	= array();
		
		$conditions	= array();
		if( !$this->env->getAcl()->hasFullAccess( $session->get( 'roleId' ) ) ){
			$projects	= array();
			foreach( $modelUser->getAllByIndex( 'userId', $session->get( 'userId' ) ) as $relation )
				$projects[$relation->projectId]	= NULL;
			$conditions['projectId']	= array_keys( $projects );
		}
		if( $filterStatus )
			$conditions['status']	= $filterStatus;

		$projects	= array();
		foreach( $modelProject->getAll( $conditions, array( 'title' => 'ASC' ) ) as $project ){
			$projects[$project->projectId]	= $project;
			$project->users	= $modelUser->getAllByIndex( 'projectId', $project->projectId );
			if( $this->useMissions )
				$project->missions	= $modelMission->countByIndex( 'projectId', $project->projectId );
		}
		
		$this->addData( 'projects', $projects );
		$this->addData( 'filterStatus', $filterStatus );
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
