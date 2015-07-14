<?php
class Tool_Hydrogen_Setup_App extends CMF_Hydrogen_Application_Web_Site{

	public function __construct( $env = NULL ){
		$this->host	= getEnv( 'HTTP_HOST' );
		$this->root	= getEnv( 'DOCUMENT_ROOT' );
		$this->path	= dirname( getEnv( 'SCRIPT_NAME' ) ).'/';
		$this->uri	= $this->root.$this->path;
		$this->url	= 'http://'.$this->host.$this->path;

		$instances	= dirname( dirname( __FILE__ ) )."/config/instances.ini";
//		if( !file_exists( $instances ) )
//			die( "Missing config/instances.ini" );
/*		$file	= new File_INI_Editor( $instances, TRUE );
		if( $file->getProperty( 'uri', 'Setup' ) !== $this->uri ){
			$file->setProperty( 'uri', $this->uri, 'Setup' );
			$file->setProperty( 'url', $this->url, 'Setup' );
			$file->setProperty( 'path', $this->path, 'Setup' );
			$file->setProperty( 'host', $this->host, 'Setup' );
		}
*/
		parent::__construct( $env );
		$this->env->clock->profiler->tick( 'app' );
	}
}
?>
