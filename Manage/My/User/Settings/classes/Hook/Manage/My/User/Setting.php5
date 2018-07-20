<?php
class Hook_Manage_My_User_Setting /*extends CMF_Hydrogen_Hook*/{

	static public function onSessionInit(){
		if( $env->has( 'session' ) ){															//  environment has session support
			if( ( $userId = $env->getSession()->get( 'userId' ) ) ){							//  an user is logged in
				$config	= Model_User_Setting::applyConfigStatic( $env, $userId, FALSE );		//  apply user configuration
				$env->set( 'config', $config, TRUE );											//  override config by user config
			}
		}
	}

	static public function onViewRegisterTab( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'manage/my/user/setting' );			//  load words
		$context->registerTab( 'setting', $words->module['tab'], 4 );							//  register main tab
	}

	static public function onUserRemove( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$data	= (object) $data;
		if( !empty( $data->userId ) ){
			$model	= new Model_User_Settings( $env );
			$model->removeByIndex( 'userId', $data->userId )
		}
	}
}
