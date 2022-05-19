<?php
class Hook_JS_Form_Optionals extends CMF_Hydrogen_Hook{

	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = [] ){
//		$config		= $env->getConfig()->getAll( 'module.js_form_optionals', TRUE );
		$context->js->addScriptOnReady( 'FormOptionals.init();' );
	}
}
