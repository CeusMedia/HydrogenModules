<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Resource_REST_Client extends Hook
{

	/**
	 *	Setup REST client resource in environment.
	 *	@static
	 *	@access		public
	 *	@param		Environment		$env		Environment instance
	 *	@param		object			$context	Hook context object
	 *	@param		object			$module		Module object
	 *	@param		public			$payload	Map of hook arguments
	 *	@return		void
	 */
	static public function onEnvConstructEnd( Environment $env, $context, $module, $payload = [] )
	{
		$env->set( 'restClient', new Resource_REST_Client( $env ) );
	}
}
