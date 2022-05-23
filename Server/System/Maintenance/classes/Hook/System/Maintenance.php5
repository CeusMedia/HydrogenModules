<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_System_Maintenance extends Hook
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
		$env->getMessenger()->noteNotice( 'System Maintenance is <strong>ON</strong>.' );
		$env->getPage()->js->addScriptOnReady( 'UI.Form.Changes.init();', 9 );
	}

	/**
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	static public function onAppDispatch( Environment $env, $context, $module, $payload = [] )
	{
		$env->getMessenger()->noteNotice( print_m( $payload, NULL, NULL, TRUE ) );
	}
}
