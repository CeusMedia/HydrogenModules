<?php
class View_Manage_My_User extends CMF_Hydrogen_View {

	public function index(){}

	public static function ___onRegisterTab( $env, $context, $module, $data ){
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

	public static function renderTabs( CMF_Hydrogen_Environment_Abstract $env, $current = 0 ){
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './manage/my/user/' );
		$env->getModules()->callHook( "MyUser", "registerTabs", $tabs/*, $data*/ );						//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}
}
?>
