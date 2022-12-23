<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_Log extends Hook
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
		$config		= $env->getConfig()->getAll( 'module.js_log.' );
		if( !$config->get( 'active' ) )
			return;

		$context->js->addModuleFile( 'Log.js' );
		$availableLogLevels		= [
			1	=> 'error',
			2	=> 'warn',
			4	=> 'info',
			8	=> 'log',
			16	=> 'debug'
		];
		$logLevel	= 0;
		foreach( $availableLogLevels as $levelInt => $levelKey ){
			if( !$config->get( 'level.'.$levelKey.'.enabled' ) )
				continue;
			$logLevel	|= $levelInt;

			// @todo implement support for configured IP addresses (per level)
		}
		$script	= sprintf( 'Log.logLevel = %d;', $logLevel );
		$context->js->addScriptOnReady( $script );
	}
}