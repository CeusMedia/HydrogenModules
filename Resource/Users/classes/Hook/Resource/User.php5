<?php
class Hook_Resource_User extends CMF_Hydrogen_Hook{

	static public function onUserRemove( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$data	= (object) $data;
		if( !empty( $userId ) ){
			$modelUser		= new Model_User( $env );
			$modelPassword	= new Model_User_Password( $env );
			$modelPassword->removeByIndex( 'userId', $data->userId );
			$modelUser->remove( $data->userId );
			if( isset( $data->counts ) )
				$data->counts['Resource_Users']	= (object) array( 'entities' => 1 );
		}
	}
}
