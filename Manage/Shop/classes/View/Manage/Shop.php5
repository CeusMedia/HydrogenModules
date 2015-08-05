<?php
class View_Manage_Shop extends CMF_Hydrogen_View{

	public function index(){
	}

	public static function ___onRegisterTab( $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'manage/shop' );									//  load words
		$context->registerTab( '', $words->tabs['dashboard'], 0 );											//  register orders tab
	}

	public static function renderTabs( CMF_Hydrogen_Environment_Abstract $env, $current = 0 ){
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './manage/shop/' );
		$env->getModules()->callHook( "ManageShop", "registerTabs", $tabs/*, $data*/ );						//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}
}
?>
