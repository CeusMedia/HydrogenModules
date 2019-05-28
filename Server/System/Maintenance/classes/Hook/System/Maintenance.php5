<?php
class Hook_System_Maintenance extends CMF_Hydrogen_Hook{

	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		$env->getMessenger()->noteNotice( 'System Maintenance is <strong>ON</strong>.' );
		$env->getPage()->js->addScriptOnReady( 'UI.Form.Changes.init();', 9 );
	}

	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onAppDispatch( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		$env->getMessenger()->noteNotice( print_m( $payload, NULL, NULL, TRUE ) );
	}
}
