<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Manage_My_User_Avatar extends View{

	public static function ___onRegisterTab( Environment $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'manage/my/user/avatar' );				//  load words
		$context->registerTab( 'avatar', $words->module['tab'], 6 );								//  register main tab
	}

	public function index(){
	}
}
