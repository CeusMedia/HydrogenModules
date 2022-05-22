<?php

use CeusMedia\HydrogenFramework\Environment;

class Hook_JS_Storage extends CMF_Hydrogen_Hook{

	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onPageApplyModules( Environment $env, $context, $module, $payload = [] ){
//		$config		= $env->getConfig()->getAll( 'module.js_optionals', TRUE );
		$script		= '$(document).ready(function(){FormOptionals.init();})';
		$context->js->addScript( $script );
	}
}
