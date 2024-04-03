<?php
/**
 *	Environment for chat server.
 *	@category		cmApps
 *	@package		Chat.Server
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Environment\Router\Recursive as RecursiveRouter;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/**
 *	Environment for chat client.
 *	@category		cmApps
 *	@package		Chat.Server
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */
class Environment extends WebEnvironment
{
	/**
	 *	Constructor, sets up all resources.
	 *	@access		public
	 *	@return		void
	 */
	public function __construct()
	{
		self::$classRouter	= RecursiveRouter::class;
		self::$configFile	= "config/config.ini";
		$this->detectSelf( FALSE );
		$this->uri	= getCwd().'/';																	//  hack for console jobs
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

	public function get( string $key, bool $strict = TRUE ): ?object
	{
		if( $key == "dbc" )
			return $this->getDatabase();
		return parent::get( $key, $strict );
	}
}
