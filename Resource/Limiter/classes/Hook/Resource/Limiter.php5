<?php
class Hook_Resource_Limiter extends CMF_Hydrogen_Hook{

	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 *	@todo		implement module main switch
	 */
	static public function onLimiterRegisterLimits( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		$config	= $env->getConfig()->getAll( 'module.resource_limiter.' );
		if( !$config->get( 'active' ) )
			return;
		$context->set( 'Limiter:isOn', TRUE );
	}

	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 *	@todo		implement module main switch
	 */
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		$logic		= Logic_Limiter::getInstance( $env );
		$env->getCaptain()->callHook( 'Limiter', 'registerLimits', $logic, array() );
	}
}
