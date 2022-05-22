<?php

use CeusMedia\HydrogenFramework\Environment;

class Hook_Resource_User extends CMF_Hydrogen_Hook
{
	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment instance
	 *	@param		object						$context	Hook context object
	 *	@param		object						$module		Module object
	 *	@param		public						$payload	Map of hook arguments
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
				$payload->counts['Resource_Users']	= (object) array( 'entities' => 1 );
		}
	}
}
