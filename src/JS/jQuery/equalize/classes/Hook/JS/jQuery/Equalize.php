<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_jQuery_Equalize extends Hook
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
		if( !$module->config['auto']->value )
			return;
		if( !( $selector = $module->config['auto.selector']->value ) )
			return;
		$params	= json_encode( array(
			'equalize'	=> $module->config['auto.dimension']->value,
			'reset'		=> $module->config['auto.reset']->value
		) );
		$context->js->addScriptOnReady( 'jQuery("'.$selector.'").equalize('.$params.');' );
	}
}
