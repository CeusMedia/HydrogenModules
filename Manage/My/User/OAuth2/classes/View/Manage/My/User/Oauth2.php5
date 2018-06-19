<?php
class View_Manage_My_User_Oauth2 extends CMF_Hydrogen_View{

	public static function ___onRegisterTab( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'manage/my/user/oauth2' );				//  load words
		$context->registerTab( 'oauth2', $words->module['tab'], 5 );								//  register main tab
	}

	public function index(){}
}
