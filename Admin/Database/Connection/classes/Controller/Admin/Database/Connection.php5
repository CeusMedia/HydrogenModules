<?php
/**
 *	Controller.
 *	@version		$Id$
 */
/**
 *	Controller.
 *	@version		$Id$
 *	@todo			implement
 *	@todo			code documentation
 */
class Controller_Admin_Database_Connection extends CMF_Hydrogen_Controller{

	public function ajaxCheck(){
		$post		= (object) $this->env->getRequest()->getAllFromSource( "POST" )->getAll();
		$status		= 0;
		$error		= NULL;
		try{
			$dsn	= new Database_PDO_DataSourceName( $post->driver, $post->name );
			if( strlen( trim( $post->host ) ) )
				$dsn->setHost( $post->host );
			if( strlen( trim( $post->port ) ) )
				$dsn->setPort( $post->port );
			if( strlen( trim( $post->username ) ) ){
				$dsn->setUsername( $post->username );
				$dsn->setPassword( $post->password );
			}
			$dbc	= new Database_PDO_Connection( $dsn, $post->username, $post->password );
			$status	= 1;
		}
		catch( Exception $e ){
			$error	= $e->getMessage();
		}
		print( json_encode( array( 'status' => $status, 'error' => $error ) ) );
		exit;
	}

	public function configure(){
		$messenger	= $this->env->getMessenger();
		$request	= $this->env->getRequest();

		$fileName	= $this->env->path.'config/config.ini';

		$driver		= trim( $request->get( 'database_driver' ) );
		
		$data	= array(
#			'database'				=> 'yes',
			'database.driver'		=> $driver,
			'database.host'			=> $driver ? trim( $request->get( 'database_host' ) ) : "",
			'database.name'			=> $driver ? trim( $request->get( 'database_name' ) ) : "",
			'database.username'		=> $driver ? trim( $request->get( 'database_username' ) ) : "",
			'database.password'		=> $driver ? trim( $request->get( 'database_password' ) ) : "",
			'database.prefix'		=> $driver ? trim( $request->get( 'database_prefix' ) ) : "",
			'database.log.error'	=> $driver ? trim( $request->get( 'database_log' ) ) : ""
		);

		try{
			$editor	= new File_INI_Editor( $fileName, FALSE );
			foreach( $data as $key => $value ){
#				if( !strlen( $data['database.driver'] ) )
#					$value	= $key == 'database' ? 'no' : '';
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
		$this->restart( './admin/database/connection' );
	}

	/**
	 *	Default action on this controller.
	 *	@access		public
	 *	@return		void
	 */
	public function index(){
		$config			= $this->env->getConfig();
		$session		= $this->env->getSession();
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'index' );

		$fileConfig		= $this->env->path.'config/config.ini';


		$this->addData( 'drivers', PDO::getAvailableDrivers() );
		$config	= array();
		if( !file_exists( $fileConfig ) )
			$messenger->noteError( $words->msgConfigMissing );
		else
			$config		= parse_ini_file( $fileConfig, FALSE );
		$config	= new ADT_List_Dictionary( $config );

		$data	= array(
			'driver'		=> $config->get( 'database.driver' ),
			'host'			=> $config->get( 'database.host' ),
			'port'			=> $config->get( 'database.port' ),
			'name'			=> $config->get( 'database.name' ),
			'prefix'		=> $config->get( 'database.prefix' ),
			'username'		=> $config->get( 'database.username' ),
			'password'		=> $config->get( 'database.password' ),
			'log'			=> $config->get( 'database.log' )
		);
		$this->addData( 'data', (object) $data );
	}
}
?>
