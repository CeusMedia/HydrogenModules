<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Manage_My_User_Oauth2 extends View{

	public static function ___onRegisterTab( Environment $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'manage/my/user/oauth2' );				//  load words
		$context->registerTab( 'oauth2', $words->module['tab'], 5 );								//  register main tab
	}

	public function index(){}
}
