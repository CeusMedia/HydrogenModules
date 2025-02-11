<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_Log extends Hook
{
	/**
	 *	@return		void
	 */
	public function onPageApplyModules(): void
	{
		$config		= $this->env->getConfig()->getAll( 'module.js_log.' );
		if( !$config->get( 'active' ) )
			return;

		$this->context->js->addModuleFile( 'Log.js' );
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
		$this->context->js->addScriptOnReady( $script );
	}
}
