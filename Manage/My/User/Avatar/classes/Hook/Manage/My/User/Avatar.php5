<?php

use CeusMedia\HydrogenFramework\Environment;

class Hook_Manage_My_User_Avatar extends CMF_Hydrogen_Hook{

	static public function onUserRemove( Environment $env, $context, $module, $data = [] ){
		$data	= (object) $data;
		if( !empty( $data->userId ) ){
			$model	= new Model_User_Avatar( $env );
			$count	= $model->removeByIndex( 'userId', $data->userId );
			if( isset( $data->counts ) )
				$data->counts['Manage_My_User_Avatar']	= (object) array( 'entities' => $count );
		}
	}
}
