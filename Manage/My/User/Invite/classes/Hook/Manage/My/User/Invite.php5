<?php
class Hook_Manage_My_User_Invite extends CMF_Hydrogen_Hook{

	static public function onUserRemove( CMF_Hydrogen_Environment $env, $context, $module, $payload ){
		$payload	= (object) $payload;
		if( !empty( $payload->userId ) ){
			$model	= new Model_User_Invite( $env );
			$count	= 0;
			$count	+= $model->removeByIndex( 'inviterId', $payload->userId );
			$count	+= $model->removeByIndex( 'invitedId', $payload->userId );
			if( isset( $payload->counts ) )
				$payload->counts['Manage_My_User_Invite']	= (object) array( 'entities' => $count );
		}
	}

	static public function onProjectRemove( CMF_Hydrogen_Environment $env, $context, $module, $payload ){
		$payload		= (object) $payload;
		if( !empty( $payload->projectId ) ){
			$model		= new Model_User_Invite( $env );
			$count		= $model->removeByIndex( 'projectId', $payload->projectId );
			if( isset( $payload->counts ) )
				$payload->counts['Manage_My_User_Invite']	= (object) array( 'entities' => $count );
		}
    }
}
