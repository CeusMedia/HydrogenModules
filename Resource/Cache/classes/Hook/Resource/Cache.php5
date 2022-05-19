<?php
class Hook_Resource_Cache extends CMF_Hydrogen_Hook{

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onEnvInitCache( CMF_Hydrogen_Environment $env, $context, $module, $payload = [] ){
		$env->set( 'cache', new Model_Cache( $env ) );
	}
}
