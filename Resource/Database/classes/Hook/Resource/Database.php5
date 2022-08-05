<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Resource_Database extends Hook
{
	/**
	 *	Create database resource when environment is calling for it.
	 *	@static
	 *	@access		public
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		object			$payload	Data object of payload data
	 *	@return		void
	 */
	static public function onEnvInitDatabase( Environment $env, $module, $context, $payload )
	{
		$payload->managers['Module_Resource_Database']	= new Resource_Database( $env );
	}
}
