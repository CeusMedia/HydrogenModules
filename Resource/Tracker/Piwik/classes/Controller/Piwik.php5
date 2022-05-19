<?php
class Controller_Piwik extends CMF_Hydrogen_Controller{

	/**
	 *	Loads connector to local Piwik installation for PHP side tracking, if enabled and available.
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment instance
	 *	@param		object						$context	Hook context object
	 *	@param		object						$module		Module object
	 *	@param		public						$arguments	Map of hook arguments
	 *	@return		void
	 */
	static public function ___onEnvInit( CMF_Hydrogen_Environment $env, $context, $module, $arguments = [] ){
		$config	= $env->getConfig()->getAll( 'module.resource_tracker_piwik.', TRUE );				//  get module configuration as dictionary
		if( !$config->get( 'active' ) || !( $id = $config->get( 'ID' ) ) )							//  piwik tracking is disabled or ID is not set
			return;
		if( !( $uri = $config->get( 'URI' ) ) )														//  URI to piwik is not defined
			return;
		if( !$config->get( 'local' ) || !( $path = $config->get( 'local.path' ) ) )					//  no local installation available
			return;
		$classFile	= rtrim( $path, " /" ).'/libs/PiwikTracker/PiwikTracker.php';					//  calculate piwik tracker class file
		@include_once $classFile;																	//  include piwik tracker class file
		if( !class_exists( 'PiwikTracker' ) )														//  include was NOT successful
			throw new RuntimeException( 'Piwik tracker inclusion failed ('.$classFile.')' );
		PiwikTracker::$URL = rtrim( $uri, " /" ).'/';												//  set URL of piwik
		$env->set( 'piwik', new PiwikTracker( $id ) );												//  bind piwik tracker instance to environment
	}

	/**
	 *	Loads connector to remove Piwik installation for client side tracking, if enabled and available.
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment instance
	 *	@param		object						$context	Hook context object
	 *	@param		object						$module		Module object
	 *	@param		public						$arguments	Map of hook arguments
	 *	@return		void
	 */
	static public function ___onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $arguments = [] ){
		$config	= $env->getConfig()->getAll( 'module.resource_tracker_piwik.', TRUE );				//  get module configuration as array map
		CMF_Hydrogen_Deprecation::getInstance()
			->setVersion( $env->getModules()->get( 'Resource_Tracker_Piwik' )->version )
			->setErrorVersion( '0.4.2' )
			->setExceptionVersion( '0.4.2' )
			->message( 'Module Resource:Tracker:Piwik is deprecated, use Resource:Tracker:Matomo instead' );
		if( !$config->get( 'active' ) || !$config->get( 'ID' ) )									//  piwik tracking is disabled or ID is not set
			return;
		if( !( $uri = $config->get( 'URI' ) ) )														//  URI to piwik is not defined
			return;
		$context->js->addUrl( rtrim( $config->get( 'URI' ), " /" ).'/piwik.js' );					//
		$config->set( 'URI', preg_replace( "@^[a-z]+://@", "", $config->get( 'URI' ) ) );			//  remove protocol since piwik init script will add it itself
		$script	= '
function initPiwik(options){
	var pkProtocol = ("https:" == document.location.protocol) ? "https" : "http";
	var pkBaseURL = pkProtocol + "://" + options.URI;
	try {
		var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", options.ID);
		piwikTracker.trackPageView();
		piwikTracker.enableLinkTracking();
	} catch( err ) {}
}
initPiwik('.json_encode( $config->getAll() ).');';
		$context->js->addScriptOnReady( $script );
	}

	/**
	 *	Extends response page by tracking pixel for clients having JavaScript disabled.
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment instance
	 *	@param		object						$context	Hook context object
	 *	@param		object						$module		Module object
	 *	@param		public						$arguments	Map of hook arguments
	 *	@return		void
	 */
	static public function ___onPageBuild( CMF_Hydrogen_Environment $env, $context, $module, $arguments = [] ){
		$config	= $env->getConfig()->getAll( 'module.resource_tracker_piwik.', TRUE );				//  get module configuration as dictionary
		if( !$config->get( 'active' ) || !( $id = $config->get( 'ID' ) ) )							//  piwik tracking is disabled or ID is not set
			return;
		if( !( $uri = $config->get( 'URI' ) ) )														//  URI to piwik is not defined
			return;
		$noscript	= UI_HTML_Tag::create( 'noscript', UI_HTML_Tag::create( 'p',					//  create noscript HTML tag
			UI_HTML_Tag::create( 'img', NULL, array(												//  create tracking image
				'src'	=> rtrim( $uri, " /" ).'/piwik.php?idsite='.$id,							//
				'style'	=> 'border: 0',																//  no borders
				'alt'	=> ''																		//  atleast empty alternative text for XHTML validity
			) )
		) );
		$context->addBody( $noscript );																//  append noscript tag to body
	}

	public function index(){
	}
}
?>
