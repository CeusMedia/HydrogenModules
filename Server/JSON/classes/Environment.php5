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
		$this->initClock();																			//  setup clock
		$this->initConfiguration();																	//  setup configuration
		$this->initModules();																		//  setup module support
		$this->initDatabase();																		//  setup database connection
		$this->initCache();																			//  setup cache support
		$this->initRequest();																		//  setup HTTP request handler
		$this->initResponse();																		//  setup HTTP response handler
		$this->initRouter();																		//  setup request router
#		$this->initLanguage();																		//  [DO NOT] setup language support
		$this->initTracker();																		//  setup server side request tracking
		$this->__onInit();																			//  call init event (implemented by extending classes)
		$this->dbc->query( 'SET NAMES "utf8"' );													//  ...
	}

	public function get( $key ){
		if( $key == "dbc" )
			return $this->getDatabase();
		return parent::get( $key );
	}

	public function getTracker(){
		return $this->tracker;
	}

	protected function initTracker() {
		$config	= $this->config;
		if( $config->get( 'tracker.enabled' ) ){
			switch( strtolower( $config->get( 'tracker.type' ) ) ){
				case 'piwik':
					if( !$config->get( 'tracker.include' ) )
						throw new RuntimeException( 'Piwik tracker include not defined in configuration (tracker.include)' );
					@include_once $config->get( 'tracker.include' );
					if( !class_exists( 'PiwikTracker' ) )
						throw new RuntimeException( 'Piwik tracker inclusion failed (config::tracker.include is invalid)' );
					PiwikTracker::$URL = $config->get( 'tracker.uri' );
					$this->tracker	= new PiwikTracker( $config->get( 'tracker.id' ) );
					break;
				case 'etracker':
				case 'google':
					throw new RuntimeException( 'Not implemented' );
					break;
			}
		}
	}
}
?>
