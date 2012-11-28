<?php
class Controller_Admin_Module_Source extends CMF_Hydrogen_Controller{

	/**	@var	Model_ModuleSource	$model		Instance of sources model */
	protected $model;
	
	protected function __onInit(){
		$this->model		= new Model_ModuleSource( $this->env );
		$this->messenger	= $this->env->getMessenger();
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

	protected function renderModuleLinkList( $modules, $linkToViewer = FALSE ){
		$list	= array();
		foreach( $modules as $module ){
			$label	= $module->title;
			if( $linkToViewer ){
				$url	= './admin/module/viewer/view/'.$module->id;
				$class	= 'icon module module-status0';
				$link	= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url ) );
				$label	= UI_HTML_Tag::create( 'span', $link, array( 'class' => $class ) );
			}
			$list[]	= UI_HTML_Tag::create( 'li', $label );
		}
		return UI_HTML_Tag::create( 'ul', $list );
	}

	public function refresh( $sourceId, $toList = TRUE ){
		$words		= (object) $this->getWords( 'msg' );

		$source		= $this->model->get( $sourceId );
		$libOld		= new CMF_Hydrogen_Environment_Resource_Module_Library_Source( $this->env, $source );
		$modulesOld	= (array) $libOld->getAll();

		$this->env->getCache()->setContext( 'Modules/'.$sourceId.'/' );
		$this->env->getCache()->flush();
		$this->env->getCache()->setContext( '' );
		$pathCache	= 'config/modules/cache/';
		if( $this->env->getConfig()->get( 'path.cache' ) )
			$pathCache	= $this->env->getConfig()->get( 'path.cache' );
		$fileCache	= $pathCache.'Sources/'.$sourceId;
		if( file_exists( $fileCache ) ){
			@unlink( $fileCache );
			$this->env->getMessenger()->noteNotice( 'Removed local module cache file <small><code>'.$fileCache.'</code></small>.' );
		}

		$libNew		= new CMF_Hydrogen_Environment_Resource_Module_Library_Source( $this->env, $source );
		$modulesNew	= (array) $libNew->getAll();

		$modulesRemoved		= array_diff_key( $modulesOld, $modulesNew );
		$modulesAdded		= array_diff_key( $modulesNew, $modulesOld );
		$modulesUpdated		= array();
		foreach( $modulesOld as $moduleKey => $module ){
			if( array_key_exists( $moduleKey, $modulesNew ) ){
				if( version_compare( $module->version, $modulesNew[$moduleKey]->version ) < 0 ){
					$modulesUpdated[$moduleKey]	= $module;
					$module->versionNew	= $modulesNew[$moduleKey]->version;
				}
			}
		}
		
		$sourceLabel	= UI_HTML_Tag::create( 'acronym', $source->id, array( 'title' => $source->title ) );
		if( $modulesAdded || $modulesRemoved || $modulesUpdated ){
			$this->messenger->noteSuccess( $words->successRefresh, $sourceLabel, count( $modulesAdded ), count( $modulesRemoved ), count( $modulesUpdated ) );
			if( $modulesAdded ){
				$list	= $this->renderModuleLinkList( $modulesAdded, TRUE );
				$this->messenger->noteNotice( $words->noticeRefreshModulesAdded, $list );
			}

			if( $modulesRemoved ){
				$list	= $this->renderModuleLinkList( $modulesRemoved, FALSE );
				$this->messenger->noteNotice( $words->noticeRefreshModulesRemoved, $list );
			}

			if( $modulesUpdated ){
				foreach( $modulesUpdated as $module ){
					$versions			= $module->version.' &rArr; '.$module->versionNew;
					$module->title		.= '&nbsp;'.UI_HTML_Tag::create( 'small', '('.$versions.')' );
				}
				$list	= $this->renderModuleLinkList( $modulesUpdated, TRUE );
				$this->messenger->noteNotice( $words->noticeRefreshModulesUpdated, $list );
			}
		}
		else
			$this->messenger->noteSuccess( $words->successRefreshNoChanges, $sourceLabel );
		
		$this->restart( './admin/module/source/edit/'.$sourceId );
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