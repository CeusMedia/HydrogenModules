<?php
class Controller_Admin_Module_Source extends CMF_Hydrogen_Controller{

	/**	@var	Model_ModuleSource	$model		Instance of sources model */
	protected $model;
	
	protected function __onInit(){
		$this->model	= new Model_ModuleSource( $this->env );
		$this->addData( 'root', getEnv( 'DOCUMENT_ROOT' ).'/' );
	}
	
	public function ajaxReadSource( $sourceId = NULL ){
		$source		= (object) array( 'id' => NULL, 'path' => NULL, 'type' => NULL );
		if( $sourceId )
			$source		= $this->model->get( $sourceId );

		$post		= $this->env->getRequest()->getAllFromSource( 'post' );
		if( $post->has( 'path' ) )
			$source->path	= $post->get( 'path' );
		if( $post->has( 'type' ) )
			$source->type	= $post->get( 'type' );

		$result		= array(
			'type'			=> $source->type,
			'path'			=> $source->path,
			'code'			=> 0,
			'data'			=> NULL,
			'error'			=> NULL,
			'access'		=> 0,
			'modules'		=> array(),
			'readable'		=> NULL,
			'writable'		=> NULL,
			'executable'	=> NULL,
		);

		try{
			switch( $source->type ){
				case 'http':
					if( !$source->path ){
						$result['code']		= -2;
						$result['error']	= 'Path not set';
					}
					else if( !Alg_Validation_Predicates::isUrl( $source->path ) ){
						$result['code']		= -3;
						$result['error']	= 'Path not valid';
					}
					else{
						try{
							$lib		= new CMF_Hydrogen_Environment_Resource_Module_Library_Source( $this->env, $source );
							$modules	= (array) $lib->getAll();

							$result['code']			= 1;
							$result['readable']		= TRUE;
							$result['writable']		= FALSE;
							$result['executable']	= FALSE;
							if( $modules ){
								ksort( $modules );
								$result['modules']		= array_values( $modules );
								$result['code']			= 2;
							}
						}
						catch( Exception $e ){
							$result['code']		= -4;
							$result['error']	= 'Exception: '.$e->getMessage();
							$result['data']		= $response;
						}
					}
					break;
				case 'folder':
					if( !$source->path ){
						$result['code']		= -2;
						$result['error']	= 'Path not set';
					}
					else if( substr( $source->path, -1 ) != "/" ){
						$result['code']		= -3;
						$result['error']	= 'Path not valid';
					}
					else if( !file_exists( $source->path ) ){
						$result['code']		= -4;
						$result['error']	= 'Path not existing';
					}
					else{
						$lib		= new CMF_Hydrogen_Environment_Resource_Module_Library_Source( $this->env, $source );
						$modules	= $lib->getAll();
						$result['modules']		= array_values( $modules );
						$result['code']			= 1;
						$result['readable']		= is_readable( $source->path );
						$result['writable']		= is_writable( $source->path );
						$result['executable']	= is_executable( $source->path );
					}
					break;
			}
		}
		catch( Exception $e ){
			$result['code']		= -1;
			$result['error']	= $e->getMessage();
		}
		$result['access']	|= $result['readable'] ? 4 : 0;
		$result['access']	|= $result['writable'] ? 2 : 0;
		$result['access']	|= $result['executable'] ? 1 : 0;
		print( json_encode( $result ) );
		exit;
	}
	
	public function add(){
		$messenger	= $this->env->getMessenger();
		$post		= $this->env->getRequest()->getAllFromSource( 'post' );
		$words		= (object) $this->getWords( 'msg' );
		if( $post->get( 'add' ) ){

			$id			= trim( $post->get( 'id' ) );
			$type		= trim( $post->get( 'type' ) );
			$title		= trim( $post->get( 'title' ) );
			$path		= trim( $post->get( 'path' ) );
			$active		= (bool) trim( $post->get( 'active' ) );
#			$username	= trim( $post->get( 'username' ) );
#			$password	= trim( $post->get( 'password' ) );
			
#			$path		= preg_replace( '@/*$@', '', $path ).'/';

			if( !strlen( $id ) )
				$this->env->getMessenger()->noteError( $words->errorIdMissing );
			if( strtolower( $id ) == "local" )
				$this->env->getMessenger()->noteError( $words->errorIdRerserved, $id );
			if( $this->model->has( $id ) )
				$this->env->getMessenger()->noteError( $words->errorIdExisting, $id );
			if( !strlen( $title ) )
				$this->env->getMessenger()->noteError( $words->errorTitleMissing );
			if( !strlen( $type ) )
				$this->env->getMessenger()->noteError( $words->errorTypeMissing );
			if( !strlen( $path ) )
				$this->env->getMessenger()->noteError( $words->errorPathMissing );
			if( !$messenger->gotError() ){
				$data		= array(
					'id'			=> $id,
					'active'		=> $active,
					'type'			=> $type,
					'title'			=> $title,
					'path'			=> $path,
#					'username'		=> $username,
#					'password'		=> $password,
				);
				$sourceId	= $this->model->add( $data );
				$messenger->noteSuccess( $words->successAdded, $id );
				$this->restart( 'edit/'.$sourceId, TRUE );
			}
		}
		$this->addData( 'id', $post->get( 'id' ) );
		$this->addData( 'path', $post->get( 'path' ) );
		$this->addData( 'title', $post->get( 'title' ) );
		$this->addData( 'active', $post->get( 'active' ) );
#		$this->addData( 'username', $post->get( 'username' ) );
	}

	public function edit( $sourceId ){
		$messenger	= $this->env->getMessenger();
		$post		= $this->env->getRequest()->getAllFromSource( 'post' );
		$words		= (object) $this->getWords( 'msg' );
		if( $post->get( 'edit' ) ){

			$id			= trim( $post->get( 'id' ) );
			$type		= trim( $post->get( 'type' ) );
			$title		= trim( $post->get( 'title' ) );
			$path		= trim( $post->get( 'path' ) );
			$active		= (bool) trim( $post->get( 'active' ) );
#			$username	= trim( $post->get( 'username' ) );
#			$password	= trim( $post->get( 'password' ) );
			
			if( !strlen( $id ) )
				$this->env->getMessenger()->noteError( $words->errorIdMissing );
			if( strtolower( $id ) == "local" )
				$this->env->getMessenger()->noteError( $words->errorIdRerserved, $id );
			if( $sourceId != $id && $this->model->has( $id ) )
				$this->env->getMessenger()->noteError( $words->errorIdExisting, $id);
			if( !strlen( $title ) )
				$this->env->getMessenger()->noteError( $words->errorTitleMissing );
			if( !strlen( $type ) )
				$this->env->getMessenger()->noteError( $words->errorTypeMissing );
			if( !strlen( $path ) )
				$this->env->getMessenger()->noteError( $words->errorPathMissing );
			if( !$messenger->gotError() ){
				$data		= array(
					'active'		=> $active,
					'type'			=> $type,
					'title'			=> $title,
					'path'			=> $path,
#					'username'		=> $username,
#					'password'		=> $password,
				);
				$this->model->edit( $sourceId, $data );
				if( $sourceId !== $id )
					$this->model->changeId( $sourceId, $id );
				$messenger->noteSuccess( $words->successEdited, $id );
				$this->restart( NULL, TRUE );
			}
		}
		$source		= $this->model->get( $sourceId );
		$source->id	= $sourceId;
		if( empty( $source->username ) )
			$source->username	= '';
		if( empty( $source->password ) )
			$source->password	= '';
		
		$this->addData( 'source', $source );
	}

	public function index(){
		$this->addData( 'sources', $this->model->getAll( FALSE ) );
	}

	protected function getModules( $sourceId, $path = NULL, $type = NULL ){
		$source		= $this->model->get( $sourceId );
	}

	public function refresh( $sourceId, $toList = TRUE ){
		$words		= (object) $this->getWords( 'msg' );
		$this->env->getMessenger()->noteSuccess( $words->successRefreshed, $sourceId );
		$this->env->getCache()->setContext( 'Modules/'.$sourceId.'/' );
		$this->env->getCache()->flush();
		$this->env->getCache()->setContext( '' );
		if( $toList )
			$this->restart( './admin/module/source' );
		$this->restart( './admin/module/source/edit/'.$sourceId );
	}
	
	public function remove( $sourceId ){
		$words		= (object) $this->getWords( 'msg' );
		$this->model->remove( $sourceId );
		$this->env->getMessenger()->noteSuccess( $words->successRemoved, $sourceId );
		$this->restart( NULL, TRUE );
	}
}
?>