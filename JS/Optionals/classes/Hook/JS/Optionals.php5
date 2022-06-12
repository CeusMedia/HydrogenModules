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
//		$config		= $env->getConfig()->getAll( 'module.js_optionals', TRUE );
		$script		= '$(document).ready(function(){FormOptionals.init();})';
		$context->js->addScript( $script );
	}
}
