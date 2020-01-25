<?php
class Hook_Resource_Database extends CMF_Hydrogen_Hook
{
	/**
	 *	Create database resource when environment is calling for it.
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		object						$payload	Data object of payload data
	 *	@return		void
	 */
	static public function onEnvInitDatabase( CMF_Hydrogen_Environment $env, $module, $context, $payload ){
		$payload->managers['Module_Resource_Database']	= new Resource_Database( $env );
	}
}
