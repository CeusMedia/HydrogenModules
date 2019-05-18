<?php
class Hook_Tracker_Matomo extends CMF_Hydrogen_Controller{

	/**
	 *	Loads connector to local Matomo installation for PHP side tracking, if enabled and available.
	 *	Adds resource 'piwik' to environment.
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment instance
	 *	@param		object						$context	Hook context object
	 *	@param		object						$module		Module object
	 *	@param		public						$payload	Map of hook arguments
	 *	@return		void
	 */
	static public function onEnvInit( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		$config	= $env->getConfig()->getAll( 'module.resource_tracker_matomo.', TRUE );			//  get module configuration as dictionary
		if( !$config->get( 'active' ) || !$config->get( 'ID' ) )								//  Matomo tracking is disabled or ID is not set
			return;
		if( !( $uri = $config->get( 'server.URL' ) ) )											//  URI to Matomo service is not defined
			return;
		if( !$config->get( 'local.active' ) || !$config->get( 'local.path' ) )					//  no local installation available
			return;
		$serverUrl	= rtrim( $config->get( 'server.URL' ), '/' ).'/';
		$localPath	= rtrim( $config->get( 'local.path' ), '/' ).'/';
		$classFile	= $localPath.'/libs/PiwikTracker/PiwikTracker.php';							//  calculate Piwik tracker class file
		@include_once $classFile;																//  include Piwik tracker class file
		if( !class_exists( 'PiwikTracker' ) )													//  include was NOT successful
			throw new RuntimeException( 'Piwik tracker inclusion failed ('.$classFile.')' );
		PiwikTracker::$URL = $serverUrl;														//  set URL of Matomo service
		$env->set( 'piwik', new PiwikTracker( $config->get( 'ID' ) ) );							//  register Piwik tracker instance in environment
	}

	/**
	 *	Loads connector to remove Matomo installation for client side tracking, if enabled and available.
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment instance
	 *	@param		object						$context	Hook context object
	 *	@param		object						$module		Module object
	 *	@param		public						$payload	Map of hook arguments
	 *	@return		void
	 */
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		$config		= $env->getConfig()->getAll( 'module.resource_tracker_matomo.', TRUE );		//  get module configuration as dictionary
		if( !$config->get( 'active' ) || !$config->get( 'ID' ) )								//  Matomo tracking is disabled or ID is not set
			return;
		if( !$config->get( 'server.active' ) || !$config->get( 'server.URL' ) )					//  do not use Matomo service
			return;
		$pathJs		= $env->getConfig()->get( 'path.scripts' );
		$context->js->addUrl( $pathJs.'module.tracker.matomo.js' );
		$serverUrl	= rtrim( $config->get( 'server.URL' ), '/' ).'/';
		$script	= '
ModuleTrackerMatomo.id = '.$config->get( 'ID' ).';
ModuleTrackerMatomo.serverUrl = '.json_encode( $serverUrl ).';
ModuleTrackerMatomo.options = '.json_encode( $config->getAll( 'option.' ) ).';
ModuleTrackerMatomo.init();';
		$context->js->addScriptOnReady( $script );

		$noscript	= UI_HTML_Tag::create( 'noscript', UI_HTML_Tag::create( 'p',				//  create noscript HTML tag
			UI_HTML_Tag::create( 'img', NULL, array(											//  create tracking image
				'src'	=> $serverUrl.'piwik.php?idsite='.$config->get( 'ID' ).'&amp;rec=1',	//
				'style'	=> 'border: 0',															//  no borders
				'alt'	=> ''																	//  atleast empty alternative text for XHTML validity
			) )
		) );
		$context->addBody( $noscript );															//  append noscript tag to body
	}
}
?>
