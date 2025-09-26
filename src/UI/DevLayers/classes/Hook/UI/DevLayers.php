<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_DevLayers extends Hook
{
	/**
	 *	...
	 *	@access		public
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	public static function onPageApplyModules( Environment $env, $context, $module, $payload = [] )
	{
		if( !$env->getConfig()->get( 'module.ui_devlayers.active' ) )
			return;
	}

	/**
	 *	Appends dev layers to page body.
	 *	@access		public
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	public static function onPageRender( Environment $env, $context, $module, $payload = [] )
	{
		if( !$env->getConfig()->get( 'module.ui_devlayers.active' ) )
			return;
		$helper		= new View_Helper_DevLayers( $env );
		$context->addBody( $helper->render() );
	}
}
