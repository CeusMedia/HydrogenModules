<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_Form_Changes extends Hook
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
		$env->getPage()->js->addScriptOnReady( 'UI.Form.Changes.init();', 9 );
	}
}
