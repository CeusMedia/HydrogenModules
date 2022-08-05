<?php

use CeusMedia\HydrogenFramework\Environment;

class View_Manage_Shop_Bridge extends View_Manage_Shop{

	public static function ___onRegisterTab( Environment $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'manage/shop/bridge' );				//  load words
		$context->registerTab( 'bridge', $words->tabs['bridges'], 15 );								//  register orders tab
	}

	public function add(){
	}

	public function edit(){
	}

	public function index(){
	}

}
?>
