<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_SelectBox extends Hook
{
	/**
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	public static function onPageApplyModules( Environment $env, $context, $module, $payload = [] )
	{
		$options	= [];
		$script		= 'jQuery("select.cmSelectBox.above").cmSelectBox('.json_encode( array_merge( $options, ['inverted' => TRUE] ) ).')';
		$context->js->addScriptOnReady( $script );

		$script		= 'jQuery("select.cmSelectBox").not(".above").cmSelectBox('.json_encode( $options ).')';
		$context->js->addScriptOnReady( $script );
	}
}
