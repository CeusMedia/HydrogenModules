<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

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
	 *	@param		array			$payload	Array of payload data
	 *	@return		void
	 */
	static public function ____onEnvInitDatabase( Environment $env, object $context, object $module, array & $payload )
	{
		$payload['managers']['Module_Resource_Database']	= new Resource_Database( $env );
	}

	/**
	 *	Create database resource when environment is calling for it.
	 *	@access		public
	 *	@return		void
	 */
	public function onEnvInitDatabase()
	{
		$this->payload['managers']['Module_Resource_Database']	= new Resource_Database( $this->env );
	}
}
