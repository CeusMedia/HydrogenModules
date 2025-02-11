<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Resource_REST_Client extends Hook
{

	/**
	 *	Setup REST client resource in environment.
	 *	@access		public
	 *	@return		void
	 */
	public function onEnvConstructEnd(): void
	{
		$this->env->set( 'restClient', new Resource_REST_Client( $this->env ) );
	}
}
