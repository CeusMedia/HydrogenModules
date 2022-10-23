<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_Storage extends Hook
{
	/**
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	static public function onPageApplyModules( Environment $env, $context, $module, $payload = [] )
	{
		if( !$module->config['active']->value )
			return;
		$pathJs		= $env->getConfig()->get( 'path.scripts' );
		$fileSuffix	= $module->config['load.minified']->value ? '.min' : '';
		$context->js->addUrl( $pathJs.'js.storage'.$fileSuffix.'.js', 3 );
	}
}
