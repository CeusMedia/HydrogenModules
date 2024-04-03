<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Resource_Limiter extends Hook
{
	/**
	 *	@return		void
	 */
	public function onLimiterRegisterLimits(): void
	{
		$config	= $this->env->getConfig()->getAll( 'module.resource_limiter.', TRUE );
		$this->context->set( 'Limiter:isOn', $config->get( 'active' ) );
	}

	/**
	 *	@return		void
	 */
	public function onPageApplyModules(): void
	{
		$config	= $this->env->getConfig()->getAll( 'module.resource_limiter.', TRUE );
		if( !$config->get( 'active' ) )
			return;
		$logic		= Logic_Limiter::getInstance( $this->env );
		$payload	= [];
		$this->env->getCaptain()->callHook( 'Limiter', 'registerLimits', $logic, $payload );
	}
}
