<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Manage_My_User extends View {

	public function index(){}

	public function remove(){}

	public static function ___onRegisterTab( Environment $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'manage/my/user' );						//  load words
		$context->registerTab( '', $words->tabs['user'], 0 );								//  register main tab
/*		if( $env->getModules()->has( 'UI_Map' ) ){													//  map module is enabled
			$model		= new Model_Customer( $env );												//  get customer model
			$customer	= $model->get( $data['customerId'] );										//  get customer data
			$disabled	= !$customer || (bool) !$customer->latitude;								//  no customer or customer not geocoded
			$label		= $words->tabs['map'];														//  get tab label
			$context->registerTab( 'map/'.$data['customerId'], $label, 2, $disabled );	//  register map tab
		}*/
	}

	public static function renderTabs( Environment $env, $current = 0 ){
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './manage/my/user/' );
		$env->getModules()->callHook( "MyUser", "registerTabs", $tabs/*, $data*/ );						//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}
}
?>
