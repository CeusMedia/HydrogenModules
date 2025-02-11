<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Hook;

class Controller_Piwik extends Hook
{
	/**
	 *	Loads connector to local Piwik installation for PHP side tracking, if enabled and available.
	 *	@access		public
	 *	@return		void
	 */
	public function onEnvInit(): void
	{
		$config = $this->env->getConfig()->getAll('module.resource_tracker_piwik.', TRUE);                //  get module configuration as dictionary
		if (!$config->get('active') || !($id = $config->get('ID')))                            //  piwik tracking is disabled or ID is not set
			return;
		if (!($uri = $config->get('URI')))                                                        //  URI to piwik is not defined
			return;
		if (!$config->get('local') || !($path = $config->get('local.path')))                    //  no local installation available
			return;
		$classFile = rtrim($path, " /") . '/libs/PiwikTracker/PiwikTracker.php';                    //  calculate piwik tracker class file
		@include_once $classFile;                                                                    //  include piwik tracker class file
		if (!class_exists('PiwikTracker'))                                                        //  include was NOT successful
			throw new RuntimeException('Piwik tracker inclusion failed (' . $classFile . ')');
		PiwikTracker::$URL = rtrim($uri, " /") . '/';                                                //  set URL of piwik
		$this->env->set('piwik', new PiwikTracker($id));                                                //  bind piwik tracker instance to environment
	}

	/**
	 *	Loads connector to remove Piwik installation for client side tracking, if enabled and available.
	 *	@access		public
	 *	@return		void
	 */
	public function onPageApplyModules(): void
	{
		$config = $this->env->getConfig()->getAll('module.resource_tracker_piwik.', TRUE);                //  get module configuration as array map
		\CeusMedia\HydrogenFramework\Deprecation::getInstance()
			->setVersion($this->env->getModules()->get('Resource_Tracker_Piwik')->version->current)
			->setErrorVersion('0.4.2')
			->setExceptionVersion('0.4.2')
			->message('Module Resource:Tracker:Piwik is deprecated, use Resource:Tracker:Matomo instead');
		if (!$config->get('active') || !$config->get('ID'))                                    //  piwik tracking is disabled or ID is not set
			return;
		if (!($uri = $config->get('URI')))                                                        //  URI to piwik is not defined
			return;
		$this->context->js->addUrl(rtrim($config->get('URI'), " /") . '/piwik.js');                    //
		$config->set('URI', preg_replace("@^[a-z]+://@", "", $config->get('URI')));            //  remove protocol since piwik init script will add it itself
		$script = '
function initPiwik(options){
	var pkProtocol = ("https:" == document.location.protocol) ? "https" : "http";
	var pkBaseURL = pkProtocol + "://" + options.URI;
	try {
		var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", options.ID);
		piwikTracker.trackPageView();
		piwikTracker.enableLinkTracking();
	} catch( err ) {}
}
initPiwik(' . json_encode($config->getAll()) . ');';
		$this->context->js->addScriptOnReady($script);
	}

	/**
	 *	Extends response page by tracking pixel for clients having JavaScript disabled.
	 *	@access		public
	 *	@return		void
	 */
	public function onPageBuild(): void
	{
		$config	= $this->env->getConfig()->getAll( 'module.resource_tracker_piwik.', TRUE );			//  get module configuration as dictionary
		if( !$config->get( 'active' ) || !( $id = $config->get( 'ID' ) ) )									//  Piwik tracking is disabled or ID is not set
			return;
		if( !( $uri = $config->get( 'URI' ) ) )																	//  URI to Piwik is not defined
			return;
		$noscript	= HtmlTag::create( 'noscript', HtmlTag::create( 'p',									//  create noscript HTML tag
			HtmlTag::create( 'img', NULL, [																//  create tracking image
				'src'	=> rtrim( $uri, " /" ).'/piwik.php?idsite='.$id,										//
				'style'	=> 'border: 0',																					//  no borders
				'alt'	=> ''																							//  at least empty alternative text for XHTML validity
			] )
		) );
		$this->context->addBody( $noscript );																			//  append noscript tag to body
	}
}
