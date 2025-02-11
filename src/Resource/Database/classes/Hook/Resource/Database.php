<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Resource_Database extends Hook
{
	/**
	 *	Create database resource when environment is calling for it.
	 *	@access		public
	 *	@return		void
	 */
	public function onEnvInitDatabase(): void
	{
		$this->payload['managers']['Module_Resource_Database']	= new Resource_Database( $this->env );
	}
}
