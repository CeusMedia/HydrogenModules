<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_Layer extends Hook
{
	/**
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 *	@todo		implement module main switch
	 */
	static public function onPageApplyModules( Environment $env, $context, $module, $payload = [] )
	{
		$config	= $env->getConfig()->getAll( 'module.js_layer.', TRUE );
		if( !$config->get( 'active' ) )
			return;

		$configSpeed		= $config->getAll( 'speed.', TRUE );
		$configButtonImage	= $config->getAll( 'button.image.', TRUE );

		$script	= '
Layer.init();
Layer.speedShow = '.$configSpeed->get( 'show' ).';
Layer.speedHide = '.$configSpeed->get( 'hide' ).';
Layer.buttonImageDownload = '.json_encode( $configButtonImage->get( 'download' ) ).';
Layer.buttonImageInfo = '.json_encode( $configButtonImage->get( 'info' ) ).';
Layer.labelButtonPrev = '.json_encode( $configButtonImage->get( 'prev.label' ) ).';
Layer.labelButtonNext = '.json_encode( $configButtonImage->get( 'next.label' ) ).';
Layer.labelButtonInfo = '.json_encode( $configButtonImage->get( 'info.label' ) ).';
Layer.labelButtonLoad = '.json_encode( $configButtonImage->get( 'download.label' ) ).';
';
		$context->js->addScriptOnReady( $script );
	}
}
