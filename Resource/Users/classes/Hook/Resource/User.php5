<?php
class Hook_Resource_User extends CMF_Hydrogen_Hook{

	static public function onUserRemove( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$data	= (object) $data;
		if( !empty( $userId ) ){
			$modelUser		= new Model_User( $this->env );
			$modelPassword	= new Model_User_Password( $this->env );
			$modelPassword->removeByIndex( 'userId', $data->userId );
			$modelUser->remove( $data->userId );
		}
	}
}
