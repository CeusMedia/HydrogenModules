<?php
class Controller_Admin_Instance extends CMF_Hydrogen_Controller{

	protected function __onInit(){
		$this->model	= new Model_Instance( $this->env );
		$this->addData( 'root', getEnv( 'DOCUMENT_ROOT' ).'/' );
	}

	public function add(){
		$messenger	= $this->env->getMessenger();
		$post		= $this->env->getRequest()->getAllFromSource( 'post' );
		if( $post->get( 'add' ) ){

			$id			= trim( $post->get( 'id' ) );
			$title		= trim( $post->get( 'title' ) );
			$path		= trim( $post->get( 'path' ) );
			$configPath	= trim( $post->get( 'configPath' ) );
			$configFile	= trim( $post->get( 'configFile' ) );
			
			$path		= preg_replace( '@^(.+)/*$@', '\\1/', $path );
			$configPath	= $configPath ? preg_replace( '@/*$@', '', $configPath ).'/' : '';
			
			if( !strlen( $title ) )
				$this->env->getMessenger()->noteError( 'Der Titel fehlt.' );
			if( !strlen( $id ) )
				$this->env->getMessenger()->noteError( 'Die ID fehlt.' );
#			if( $path == '/' )
#				$this->env->getMessenger()->noteError( 'Der Pfad fehlt.' );
			if( !$messenger->gotError() ){
				$data		= array(
					'id'			=> $id,
					'title'			=> $title,
					'path'			=> $path,
				);
				if( $configPath != '/' && $configPath != 'config/' )
					$data['configPath']	= $configPath;
				if( $configFile != 'config.ini' )
					$data['configFile']	= $configFile;
				$instanceId	= $this->model->add( $data );
				$messenger->noteSuccess( 'Die Instanz wurde hinzugefügt.' );
				$this->restart( 'edit/'.$instanceId, TRUE );
			}
		}
		$this->addData( 'id', $post->get( 'id' ) );
		$this->addData( 'path', $post->get( 'path' ) );
		$this->addData( 'title', $post->get( 'title' ) );
		$this->addData( 'configPath', $post->get( 'configPath' ) );
		$this->addData( 'configFile', $post->get( 'configFile' ) );
	}

	public function createConfig( $instanceId ){
		$messenger	= $this->env->getMessenger();
		$instance	= $this->model->get( $instanceId );

		if( !preg_match( '/^\//', $instance->path ) )
			$instance->path	= getEnv( 'DOCUMENT_ROOT' ).'/'.$instance->path;
		if( empty( $instance->configPath ) )
			$instance->configPath	= 'config/';
		if( empty( $instance->configFile ) )
			$instance->configFile	= 'config.ini';
		
		$fileName	= $instance->path.$instance->configPath.$instance->configFile;
#		remark( 'Config file: '.$fileName );
		$data		= array(
			'app.name'			=> $instance->title,
			'path.logs'				=> 'logs/',
			'path.templates'		=> 'templates/',
			'path.locales'			=> 'locales/',
			'path.themes'			=> 'themes/',
			'path.scripts'			=> 'scripts/',
			'path.scripts.lib'		=> '',
			'layout.primer'			=> '',
			'layout.theme'			=> 'custom',
			'locale.allowed'		=> 'en',
			'locale.default'		=> 'en',
		);

		try{
			File_Writer::save( $fileName, '', 0777 );												//  @todo use file owner from Setup Tool config
			$editor	= new File_INI_Editor( $fileName, FALSE );
			foreach( $data as $key => $value )
				$editor->addProperty( $key, $value );
			$messenger->noteSuccess( 'Die Konfigurationsdatei "'.$fileName.'" wurde erstellt.' );
		}
		catch( Exception $e ){
			$messenger->noteError( 'Die Konfigurationsdatei "'.$fileName.'" konnte nicht erstellt werden:<br/>'.$e->getMessage() );
		}
		$this->restart( './admin/instance/edit/'.$instanceId );
	}
		

	public function configureDatabase( $instanceId ){
		$messenger	= $this->env->getMessenger();
		$instance	= $this->model->get( $instanceId );
		$request	= $this->env->getRequest();

		if( !preg_match( '/^\//', $instance->path ) )
			$instance->path	= getEnv( 'DOCUMENT_ROOT' ).'/'.$instance->path;
		if( empty( $instance->configPath ) )
			$instance->configPath	= 'config/';
		if( empty( $instance->configFile ) )
			$instance->configFile	= 'config.ini';
		
		$fileName	= $instance->path.$instance->configPath.$instance->configFile;

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
			$editor	= new File_INI_Editor( $fileName, FALSE );
			foreach( $data as $key => $value ){
				if( !strlen( $data['database.driver'] ) )
					$value	= $key == 'database' ? 'no' : '';
				if( $editor->hasProperty( $key ) )
					$editor->setProperty( $key, $value );
				else
					$editor->addProperty( $key, $value );
			}
			$messenger->noteSuccess( 'Die Datenbankeinstellungen wurden gespeichert.' );
		}
		catch( Exception $e ){
			$messenger->noteError( 'Die Datenbankeinstellungen konnten nicht gespeichert werden:<br/>'.$e->getMessage() );
		}
		$this->restart( './admin/instance/edit/'.$instanceId );
	}
	
	public function createPath( $instanceId, $path = NULL ){
		$messenger	= $this->env->getMessenger();
		$instance	= $this->model->get( $instanceId );
		$path		= base64_decode( $path );
#		print_m( $instance );
		if( !preg_match( '/^\//', $instance->path ) )
			$instance->path	= getEnv( 'DOCUMENT_ROOT' ).'/'.$instance->path;
		$path	= $instance->path.$path;
		if( Folder_Editor::createFolder( $path, 0777 ) )											//  @todo set folder owner by Setup "Module Config Pair"
			$messenger->noteSuccess( 'Der Pfad "'.$path.'" wurde erzeugt.' );
		else
			$messenger->noteError( 'Der Pfad "'.$path.'" konnte nicht erzeugt werden.' );
		$this->restart( './admin/instance/edit/'.$instanceId );
	}
	
	public function edit( $instanceId ){
		$messenger	= $this->env->getMessenger();
		$post		= $this->env->getRequest()->getAllFromSource( 'post' );
		if( $post->get( 'edit' ) ){

			$id			= trim( $post->get( 'id' ) );
			$title		= trim( $post->get( 'title' ) );
			$path		= trim( $post->get( 'path' ) );
			$configPath	= trim( $post->get( 'configPath' ) );
			$configFile	= trim( $post->get( 'configFile' ) );

			$path		= preg_replace( '@^(.+)/*$@', '\\1/', $path );
			$configPath	= $configPath ? preg_replace( '@/*$@', '', $configPath ).'/' : '';

			if( !strlen( $title ) )
				$this->env->getMessenger()->noteError( 'Der Titel fehlt.' );
			if( !strlen( $id ) )
				$this->env->getMessenger()->noteError( 'Die ID fehlt.' );
#			if( $path == '/' )
#				$this->env->getMessenger()->noteError( 'Der Pfad fehlt.' );
			if( !$messenger->gotError() ){
				$data		= array(
					'title'		=> $title,
					'path'		=> $path,
				);
				if( $configPath != '/' && $configPath != 'config/' )
					$data['configPath']	= $configPath;
				if( $configFile != 'config.ini' )
					$data['configFile']	= $configFile;
				$this->model->edit( $instanceId, $data );
				if( $instanceId !== $id )
					$this->model->changeId( $instanceId, $id );
				$messenger->noteSuccess( 'Die Instanz wurde gespeichert.' );
				$this->restart( './admin/instance/edit/'.$id );
			}
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
		$this->model->remove( $instanceId );
		$this->restart( NULL, TRUE );
	}
}
?>
