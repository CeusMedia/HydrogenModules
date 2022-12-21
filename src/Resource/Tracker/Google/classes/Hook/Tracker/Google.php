<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Tracker_Google extends Hook
{
	/**
	 *	Loads connector Google tracking, if enabled and available.
	 *	@static
	 *	@access		public
	 *	@param		Environment		$env		Environment instance
	 *	@param		object			$context	Hook context object
	 *	@param		object			$module		Module object
	 *	@param		public			$payload	Map of hook arguments
	 *	@return		void
	 */
	static public function onPageApplyModules( Environment $env, $context, $module, $payload = [] )
	{
		$config		= $env->getConfig()->getAll( 'module.resource_tracker_google.', TRUE );		//  get module configuration as dictionary
		if( !$config->get( 'active' ) || !$config->get( 'option.trackingID' ) )					//  Google tracking is disabled or ID is not set
			return;
		$script	= '
(function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,"script","//www.google-analytics.com/analytics.js","ga");
ga("create", settings.Resource_Tracker_Google.option_trackingID, "auto");
ga("set", "anonymizeIp", settings.Resource_Tracker_Google.option_anonymizeIP);
ga("send", "pageview");';
		$context->runScript( $script, 9 );

//		$context->addBody( $noscript );															//  append noscript tag to body
	}
}
