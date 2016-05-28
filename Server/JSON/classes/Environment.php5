<?php
/**
 *	Environment for chat server.
 *	@category		cmApps
 *	@package		Chat.Server
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Environment.php5 3022 2012-06-26 20:08:10Z christian.wuerker $
 */
/**
 *	Environment for chat client.
 *	@category		cmApps
 *	@package		Chat.Server
 *	@extends		CMF_Hydrogen_Environment
 *	@uses			Logic
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Environment.php5 3022 2012-06-26 20:08:10Z christian.wuerker $
 */
class Environment extends CMF_Hydrogen_Environment_Web {


	/**
	 *	Constructor, sets up all resources.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct() {
		self::$classRouter	= 'CMF_Hydrogen_Environment_Router_Recursive';
		self::$configFile	= "config/config.ini";
		$this->initClock();																			//  setup clock
		$this->initConfiguration();																	//  setup configuration
		$this->initModules();																		//  setup module support
		$this->initDatabase();																		//  setup database connection
		$this->initCache();																			//  setup cache support
		$this->initRequest();																		//  setup HTTP request handler
		$this->initResponse();																		//  setup HTTP response handler
		$this->initRouter();																		//  setup request router
		$this->initLanguage();																		//  [DO NOT] setup language support
		$this->initPage();
		$this->__onInit();																			//  call init event (implemented by extending classes)
		if( $this->getModules()->has( 'Resource_Database' ) )
			$this->dbc->query( 'SET NAMES "utf8"' );												//  ...
	}

	public function get( $key, $strict = TRUE ){
		if( $key == "dbc" )
			return $this->getDatabase();
		return parent::get( $key, $strict );
	}
}
?>
