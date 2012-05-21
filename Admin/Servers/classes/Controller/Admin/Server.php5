<?php
class Controller_Admin_Server extends CMF_Hydrogen_Controller{

	public function add(){
		$model	= new Model_Server( $this->env );
		$words	= $this->getWords( 'add' );
		$post	= $this->env->getRequest()->getAllFromSource( 'post' );
		if( $post->get( 'add' ) ){
			if( !strlen( trim( $post->get( 'title' ) ) ) )
				$this->env->getMessenger()->noteError( $words->msgTitleMissing );
			else{
				$data	= array(
					'status'		=> $post->get( 'status' ),
					'title'			=> $post->get( 'title' ),
					'description'	=> $post->get( 'description' ),
					'createdAt'		=> time(),
				);
				$serverId	= $model->add( $data );
				$this->env->getMessenger()->noteSuccess( $words->msgSuccess );
				$this->restart( NULL, TRUE );
			}
		}
		$server	= array();
		foreach( $model->getColumns() as $column )
			$server[$column]	= (string) $post->get( $column );
		$this->addData( 'server', (object) $server );
	}

	public function addProject( $serverId ){
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= $this->getWords( 'addProject' );
		$model			= new Model_Server_Project( $this->env );
		$data	= array(
			'serverId'			=> $serverId,
			'projectId'			=> $request->get( 'projectId' ),
			'projectVersionId'	=> $request->get( 'projectVersionId' ),
			'status'			=> $request->get( 'status' ),
			'title'				=> $request->get( 'title' ),
			'description'		=> $request->get( 'description' ),
			'createdAt'			=> time(),
		);
		$model->add( $data );
		$messenger->noteSuccess( $words->msgSuccess );
		$this->restart( './admin/server/edit/'.$serverId );
	}

	public function edit( $serverId ){
		$post	= $this->env->getRequest()->getAllFromSource( 'post' );
		$words	= $this->getWords( 'edit' );
		$model	= new Model_Server( $this->env );
		if( $post->get( 'edit' ) ){
			if( !strlen( trim( $post->get( 'title' ) ) ) )
				$this->env->getMessenger()->noteError( $words->msgTitleMissing );
			else{
				$data	= array(
					'status'		=> $post->get( 'status' ),
					'title'			=> $post->get( 'title' ),
					'description'	=> $post->get( 'description' ),
					'createdAt'		=> time(),
				);
				$model->edit( $serverId, $data );
				$this->env->getMessenger()->noteSuccess( $words->msgSuccess );
				$this->restart( NULL, TRUE );
			}
		}
		$modelProject			= new Model_Project( $this->env );
		$modelProjectVersion	= new Model_Project_Version( $this->env );
		$modelRelation			= new Model_Server_Project( $this->env );
		$this->addData( 'server', $model->get( $serverId ) );
		$this->addData( 'projects', $modelProject->getAll() );
		$relations	= array();
		foreach( $modelRelation->getAllByIndex( 'serverId', $serverId ) as $relation ){
			if( $relation->projectVersionId ){
				$relation->projectVersion	= $modelProjectVersion->get( $relation->projectVersionId );
				$relation->project			= $modelProject->get( $relation->projectVersion->projectId );
			}
			$relations[]	= $relation;
		}
		$this->env->getLanguage()->load( 'admin/project' );
		$this->addData( 'wordsProjectVersionStates', (array) $this->getWords( 'version-states', 'admin/project' ) );
		$this->addData( 'serverProjects', $relations );
	}

	public function filter(){}
	
	public function index(){
		$session	= $this->env->getSession();
		$model		= new Model_Server( $this->env );
		$conditions	= array();
		$this->addData( 'servers', $model->getAll( $conditions ) );
	}

	public function view( $serverId ){
		$model	= new Model_Server( $this->env );
		$server	= $model->get( $serverId );
		if( !$server )
			$this->env->getMessenger()->noteError( $this->getWords( 'view' )->msgErrorInvalidId );
		$this->addData( 'server', $server );
	}

	public function remove( $serverId ){
		$model	= new Model_Server( $this->env );
		$this->restart( NULL, TRUE );
	}

	public function removeProject( $serverProjectId ){
		$model	= new Model_Server_Project( $this->env );
		$this->restart( NULL, TRUE );
	}
}
?>