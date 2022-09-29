<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Resource_User extends Hook
{
	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		Environment		$env		Environment instance
	 *	@param		object			$context	Hook context object
	 *	@param		object			$module		Module object
	 *	@param		array			$payload	Map of hook arguments
	 *	@return		void
	 */
	static public function onUserRemove( Environment $env, $context, $module, $payload = [] )
	{
		$payload	= (object) $payload;
		if( !empty( $payload->userId ) ){
			$modelUser		= new Model_User( $env );
			$modelPassword	= new Model_User_Password( $env );
			$modelPassword->removeByIndex( 'userId', $payload->userId );
			$modelUser->remove( $payload->userId );
			if( isset( $payload->counts ) )
				$payload->counts['Resource_Users']	= (object) ['entities' => 1];
		}
	}
}
