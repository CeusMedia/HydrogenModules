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

		$messenger->noteFailure( 'Not implemented yet.' );
/*		try{
		}
		catch( Exception $e ){
			$messenger->noteError( 'Die Datenbankeinstellungen konnten nicht gespeichert werden:<br/>'.$e->getMessage() );
		}*/
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


		$this->addData( 'drivers', PDO::getAvailableDrivers() );
		$config	= array();
		if( !$this->env->getModules()->has( 'Resource_Database' ) ){
			$messenger->noteError( $words->msgConfigMissing );
			$this->restart();
		}
		$config	= $this->env->getConfig()->getAll( 'module.resource_database.', TRUE );
		$data	= array(
			'driver'		=> $config->get( 'access.driver' ),
			'host'			=> $config->get( 'access.host' ),
			'port'			=> $config->get( 'access.port' ),
			'name'			=> $config->get( 'access.name' ),
			'prefix'		=> $config->get( 'access.prefix' ),
			'username'		=> $config->get( 'access.username' ),
			'password'		=> $config->get( 'access.password' ),
			'log'			=> $config->get( 'log.errors' )
		);
		$this->addData( 'data', (object) $data );
	}
}
?>
