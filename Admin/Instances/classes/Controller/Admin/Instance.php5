 <?php
class Controller_Admin_Instance extends CMF_Hydrogen_Controller{

	protected $moduleConfig;

	protected function __onInit(){
		$this->model		= new Model_Instance( $this->env );
		$this->messenger	= $this->env->getMessenger();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.admin_instances.', TRUE );
		$this->addData( 'root', getEnv( 'DOCUMENT_ROOT' ).'/' );
	}

	public function add(){
		$post		= $this->env->getRequest()->getAllFromSource( 'post' );
		if( $post->get( 'add' ) ){
			$id			= trim( $post->get( 'id' ) );
			$title		= trim( $post->get( 'title' ) );
			$protocol	= trim( $post->get( 'protocol' ) );
			$host		= trim( $post->get( 'host' ) );
			$path		= trim( $post->get( 'path' ) );
			$uri		= trim( $post->get( 'uri' ) );
			$configPath	= trim( $post->get( 'configPath' ) );
			$configFile	= trim( $post->get( 'configFile' ) );

			$path		= str_replace( array( " ", "../" ), "", $path );							//  secure path
			$path		= preg_replace( '@/*$@', '', $path ).'/';									//  add trailing slash to path
			$path		= '/'.preg_replace( '@^/*@', '', $path );									//  add leading slash to path

			$uri		= str_replace( array( " ", "../" ), "", $uri );								//  secure URI
			$uri		= preg_replace( '@/*$@', '', $uri ).'/';									//  add trailing slash to URI
			$uri		= '/'.preg_replace( '@^/*@', '', $uri );									//  add leading slash to URI

			$configPath	= $configPath ? preg_replace( '@/*$@', '', $configPath ).'/' : '';			//  add trailing slash to config path

			if( !strlen( $title ) )
				$this->env->getMessenger()->noteError( 'Der Titel fehlt.' );
			if( !strlen( $id ) )
				$this->env->getMessenger()->noteError( 'Die ID fehlt.' );
			if( $this->moduleConfig->get( 'lock' ) ){												//  locking is enabled
				$lock	= $this->moduleConfig->getAll( 'lock.', TRUE );
				if( strlen( $lockProtocol = trim( $lock->get( 'protocol' ) ) ) )					//  a locked protocol has been set
					$protocol	= $lockProtocol;													//  override post with locked protocol
				if( strlen( $lockHost = trim( $lock->get( 'host' ) ) ) )							//  a locked host has been set
					$host	= $lockHost;															//  override post with locked host
				if( strlen( $lockPath = trim( $lock->get( 'path' ) ) ) )							//  a lock path has been set
					if( substr( $path, 0, strlen( $lockPath ) ) !== $lockPath )						//  but is not the beginning of given URI
						$this->env->getMessenger()->noteError( 'Der Pfad muss mit "'.$lockPath.'" beginnen.' );
				if( strlen( $lockUri = trim( $lock->get( 'uri' ) ) ) )								//  a lock URI has been set
					if( substr( $uri, 0, strlen( $lockUri ) ) !== $lockUri )						//  but is not the beginning of given URI
						$this->env->getMessenger()->noteError( 'Der absolute Pfad muss mit "'.$lockUri.'" beginnen.' );
			}
#			if( $path == '/' )
#				$this->env->getMessenger()->noteError( 'Der Pfad fehlt.' );
			if( !$this->messenger->gotError() ){
				$data		= array(
					'id'			=> $id,
					'title'			=> $title,
					'protocol'		=> $protocol,
					'host'			=> $host,
					'path'			=> $path,
					'uri'			=> $uri,
				);
				if( $configPath != '/' && $configPath != 'config/' )
					$data['configPath']	= $configPath;
				if( $configFile != 'config.ini' )
					$data['configFile']	= $configFile;
				$instanceId	= $this->model->add( $data );
				$this->messenger->noteSuccess( 'Die Instanz wurde hinzugefügt.' );
				$this->restart( 'edit/'.$instanceId, TRUE );
			}
		}
		$this->addData( 'id', $post->get( 'id' ) );
		$this->addData( 'protocol', $post->get( 'protocol' ) );
		$this->addData( 'host', $post->get( 'host' ) );
		$this->addData( 'path', $post->get( 'path' ) ? $post->get( 'path' ) : '/' );
		$this->addData( 'uri', $post->get( 'uri' ) );
		$this->addData( 'title', $post->get( 'title' ) );
		$this->addData( 'configPath', $post->get( 'configPath' ) );
		$this->addData( 'configFile', $post->get( 'configFile' ) );
	}

	public function createConfig( $instanceId ){
		$instance	= $this->model->get( $instanceId );

#		if( !preg_match( '/^\//', $instance->path ) )
#			$instance->path	= getEnv( 'DOCUMENT_ROOT' ).'/'.$instance->path;
		if( empty( $instance->configPath ) )
			$instance->configPath	= 'config/';
		if( empty( $instance->configFile ) )
			$instance->configFile	= 'config.ini';

		$fileName	= $instance->uri.$instance->configPath.$instance->configFile;
#		remark( 'Config file: '.$fileName );
		$data		= array(
			'app.name'			=> $instance->title,
			'app.version'		=> '0.1a',
			'path.logs'				=> 'logs/',
			'path.templates'		=> 'templates/',
			'path.locales'			=> 'locales/',
			'path.themes'			=> 'themes/',
			'path.scripts'			=> 'scripts/',
			'path.scripts.lib'		=> '',
			'layout.primer'			=> '',
			'layout.theme'			=> 'custom',
			'locale.allowed'		=> $this->moduleConfig->get( 'config.locale.allowed' ),
			'locale.default'		=> $this->moduleConfig->get( 'config.locale.default' ),
		);

		try{
			FS_File_Writer::save( $fileName, '', 0777 );												//  @todo use file owner from Setup Tool config
			$editor	= new FS_File_INI_Editor( $fileName, FALSE );
			foreach( $data as $key => $value )
				$editor->addProperty( $key, $value );
			$this->messenger->noteSuccess( 'Die Konfigurationsdatei "'.$fileName.'" wurde erstellt.' );
		}
		catch( Exception $e ){
			$this->messenger->noteError( 'Die Konfigurationsdatei "'.$fileName.'" konnte nicht erstellt werden:<br/>'.$e->getMessage() );
		}
		$this->restart( './admin/instance/edit/'.$instanceId );
	}


	public function configureDatabase( $instanceId ){
		$instance	= $this->model->get( $instanceId );
		$request	= $this->env->getRequest();

#		if( !preg_match( '/^\//', $instance->path ) )
#			$instance->path	= getEnv( 'DOCUMENT_ROOT' ).'/'.$instance->path;
		if( empty( $instance->configPath ) )
			$instance->configPath	= 'config/';
		if( empty( $instance->configFile ) )
			$instance->configFile	= 'config.ini';

		$fileName	= $instance->uri.$instance->configPath.$instance->configFile;

		$data	= array(
			'database'				=> 'yes',
			'database.driver'		=> trim( $request->get( 'database_driver' ) ),
			'database.host'			=> trim( $request->get( 'database_host' ) ),
			'database.name'			=> trim( $request->get( 'database_name' ) ),
			'database.username'		=> trim( $request->get( 'database_username' ) ),
			'database.password'		=> trim( $request->get( 'database_password' ) ),
			'database.prefix'		=> trim( $request->get( 'database_prefix' ) ),
			'database.log.error'	=> trim( $request->get( 'database_log' ) )
		);

		try{
			$editor	= new FS_File_INI_Editor( $fileName, FALSE );
			foreach( $data as $key => $value ){
				if( !strlen( $data['database.driver'] ) )
					$value	= $key == 'database' ? 'no' : '';
				if( $editor->hasProperty( $key ) )
					$editor->setProperty( $key, $value );
				else
					$editor->addProperty( $key, $value );
			}
			$this->messenger->noteSuccess( 'Die Datenbankeinstellungen wurden gespeichert.' );
		}
		catch( Exception $e ){
			$this->messenger->noteError( 'Die Datenbankeinstellungen konnten nicht gespeichert werden:<br/>'.$e->getMessage() );
		}
		$this->restart( './admin/instance/edit/'.$instanceId );
	}

	public function createPath( $instanceId, $path = NULL ){
		$instance	= $this->model->get( $instanceId );
		$path		= base64_decode( $path );
#		print_m( $instance );
#		if( !preg_match( '/^\//', $instance->path ) )
#			$instance->path	= getEnv( 'DOCUMENT_ROOT' ).'/'.$instance->path;
		$path	= $instance->uri.$path;
		if( FS_Folder_Editor::createFolder( $path, 0777 ) )											//  @todo set folder owner by Setup "Module Config Pair"
			$this->messenger->noteSuccess( 'Der Pfad "'.$path.'" wurde erzeugt.' );
		else
			$this->messenger->noteError( 'Der Pfad "'.$path.'" konnte nicht erzeugt werden.' );
		$this->restart( './admin/instance/edit/'.$instanceId );
	}

	public function edit( $instanceId ){
		if( !$this->model->has( $instanceId ) ){
			$this->env->getMessenger()->noteError( 'Invalid instance' );
			$this->restart( './admin/instance' );
		}

		$module		= $this->env->getConfig()->getAll( 'module.admin_instances.', TRUE );
		$post		= $this->env->getRequest()->getAllFromSource( 'post' );
		if( $post->get( 'edit' ) ){
			$id			= trim( $post->get( 'id' ) );
			$title		= trim( $post->get( 'title' ) );
			$path		= trim( $post->get( 'path' ) );
			$protocol	= trim( $post->get( 'protocol' ) );
			$host		= trim( $post->get( 'host' ) );
			$uri		= trim( $post->get( 'uri' ) );
			$configPath	= trim( $post->get( 'configPath' ) );
			$configFile	= trim( $post->get( 'configFile' ) );

			$path		= str_replace( array( " ", "../" ), "", $path );							//  secure path
			$path		= preg_replace( '@/*$@', '', $path ).'/';									//  add trailing slash to path
			$path		= '/'.preg_replace( '@^/*@', '', $path );									//  add leading slash to path

			$uri		= str_replace( array( " ", "../" ), "", $uri );								//  secure URI
			$uri		= preg_replace( '@/*$@', '', $uri ).'/';									//  add trailing slash to URI
			$uri		= '/'.preg_replace( '@^/*@', '', $uri );									//  add leading slash to URI

			$configPath	= $configPath ? preg_replace( '@/*$ 	@', '', $configPath ).'/' : '';			//  add trailing slash to config path

			if( !strlen( $title ) ){
				$this->env->getMessenger()->noteError( 'Der Titel fehlt.' );
				$this->restart( 'edit/'.$instanceId, TRUE );
			}
			if( !strlen( $id ) ){
				$this->env->getMessenger()->noteError( 'Die ID fehlt.' );
				$this->restart( 'edit/'.$instanceId, TRUE );
			}
			if( $this->moduleConfig->get( 'lock' ) ){															//  locking is enabled
				$lock	= $this->moduleConfig->getAll( 'lock.', TRUE );
				if( strlen( $lockProtocol = trim( $lock->get( 'protocol' ) ) ) )					//  a locked protocol has been set
					$protocol	= $lockProtocol;													//  override post with locked protocol
				if( strlen( $lockHost = trim( $lock->get( 'host' ) ) ) )							//  a locked host has been set
					$host	= $lockHost;															//  override post with locked host
				if( strlen( $lockPath = trim( $lock->get( 'path' ) ) ) ){							//  a lock path has been set
					if( substr( $path, 0, strlen( $lockPath ) ) !== $lockPath ){					//  but is not the beginning of given URI
						$this->env->getMessenger()->noteError( 'Der Pfad muss mit "'.$lockPath.'" beginnen.' );
						$this->restart( 'edit/'.$instanceId, TRUE );
					}
				}
				if( strlen( $lockUri = trim( $lock->get( 'uri' ) ) ) ){								//  a lock URI has been set
					if( substr( $uri, 0, strlen( $lockUri ) ) !== $lockUri ){						//  but is not the beginning of given URI
						$this->env->getMessenger()->noteError( 'Der absolute Pfad muss mit "'.$lockUri.'" beginnen.' );
						$this->restart( 'edit/'.$instanceId, TRUE );
					}
				}
			}
			$data		= array(
				'title'		=> $title,
				'protocol'	=> $protocol,
				'host'		=> $host,
				'path'		=> $path,
				'uri'		=> $uri,
			);
			if( $configPath != '/' && $configPath != 'config/' )
				$data['configPath']	= $configPath;
			if( $configFile != 'config.ini' )
				$data['configFile']	= $configFile;
			$this->model->edit( $instanceId, $data );
			if( $instanceId !== $id )
				$this->model->changeId( $instanceId, $id );
			$this->messenger->noteSuccess( 'Die Instanz wurde gespeichert.' );
			$this->restart( './admin/instance/edit/'.$id );
		}
		$instance		= $this->model->get( $instanceId );
		$instance->id	= $instanceId;
		if( empty( $instance->configPath ) )
			$instance->configPath	= '';
		if( empty( $instance->configFile ) )
			$instance->configFile	= '';
		$this->addData( 'instance', $instance );
	}

	public function index(){
		$this->addData( 'instances', $this->model->getAll() );
	}

	public function remove( $instanceId ){
		$instanceId	= trim( $instanceId );
		if( !$this->model->has( $instanceId ) ){
			$this->messenger->noteError( 'Die aufgerufene Instanz existiert nicht. Weiterleitung zur Übersicht.' );
			$this->restart( './admin/instance' );
		}
		$instance	= $this->model->get( $instanceId );
		$this->messenger->noteSuccess( 'Die Instanz "'.$instance->title.'" wurde abgemeldet. <small class="hint muted">Der Instanzordner wurde dabei <b>nicht gelöscht</b></small>.' );
		$this->model->remove( $instanceId );

		if( $this->env->getSession()->get( 'instanceId' ) === $instanceId )
			$this->env->getSession()->remove( 'instanceId' );

		$this->restart( NULL, TRUE );
	}

	public function select( $instanceId = NULL ){
		if( strlen( trim( $instanceId ) ) ){														//  an instance has been given
			if( !( $instance = $this->model->get( $instanceId ) ) )									//  instance is not existing
				$this->messenger->noteError( 'Requested instance "'.$instanceId.'" is not existing.' );
			if( $instanceId !== $this->env->getSession()->get( 'instanceId' ) )						//  instance differs from current
				$this->messenger->noteNotice( 'Instanz ausgewählt: <cite>'.$instance->title.'</cite>' );
		}
		$this->env->getSession()->set( 'instanceId', $instanceId );									//  unset instance or set new instance
		$this->restart( $this->env->getRequest()->get( 'forward' ) );								//  restart or redirect
	}
}
?>
