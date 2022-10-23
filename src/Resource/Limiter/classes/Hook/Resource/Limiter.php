<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Resource_Limiter extends Hook
{
	/**
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	static public function onLimiterRegisterLimits( Environment $env, $context, $module, $payload = [] )
	{
		$config	= $env->getConfig()->getAll( 'module.resource_limiter.', TRUE );
		$context->set( 'Limiter:isOn', $config->get( 'active' ) );
	}

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
		$config	= $env->getConfig()->getAll( 'module.resource_limiter.', TRUE );
		if( !$config->get( 'active' ) )
			return;
		$logic		= Logic_Limiter::getInstance( $env );
		$env->getCaptain()->callHook( 'Limiter', 'registerLimits', $logic, [] );
	}
}
