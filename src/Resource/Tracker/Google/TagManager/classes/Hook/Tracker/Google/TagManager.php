<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment;

class Hook_Tracker_Google_TagManager extends Controller{

	/**
	 *	Extends response page by Google Tag Manager invocation.
	 *	@static
	 *	@access		public
	 *	@param		Environment		$env		Environment instance
	 *	@param		object			$context	Hook context object
	 *	@param		object			$module		Module object
	 *	@param		array			$payload	Map of hook arguments
	 *	@return		void
	 */
	static public function onPageApplyModules( Environment $env, object $context, $module, array & $payload ){
		$configKey	= 'module.resource_tracker_google_tagmanager.';
		$config		= $env->getConfig()->getAll( $configKey, TRUE );								//  get module configuration as dictionary
		if( !$config->get( 'active' ) || !$config->get( 'ID' ) )									//  module is disabled or ID is not set
			return;
		$baseUrl	= 'https://www.googletagmanager.com/';

		$script		= "
	(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'".$baseUrl."gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','".$config->get('ID')."');";
		$script		= HtmlTag::create( 'script', $script, array(
			'type'	=> 'text/javascript',
		) );
		$context->addHead( $script, 1 );

		$iframe		= HtmlTag::create( 'iframe', array(
			'src'		=> $baseUrl.'ns.html?id='.$config->get( 'ID' ),
			'height'	=> 0,
			'width'		=> 0,
			'style'		=> 'display:none;visibility:hidden'
		) );
		$context->addBody( HtmlTag::create( 'noscript', $iframe ) );							//  prepend noscript tag to body
	}
}
