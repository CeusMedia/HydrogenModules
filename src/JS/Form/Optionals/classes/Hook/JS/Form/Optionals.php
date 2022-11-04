<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_Form_Optionals extends Hook
{
	/**
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	public static function onPageApplyModules( Environment $env, object $context, $module, array & $payload = [] )
	{
//		$config		= $env->getConfig()->getAll( 'module.js_form_optionals', TRUE );
		$context->js->addScriptOnReady( 'FormOptionals.init();' );
	}
}
