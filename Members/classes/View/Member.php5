<?php
class View_Member extends CMF_Hydrogen_View{

	public static function ___onRegisterTab( $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'member' );			//  load words
		$context->registerTab( '', $words->tabs['index'], 0 );					//  register main tab
		$context->registerTab( 'search', $words->tabs['search'], 1 );			//  register main tab
	}

	public function index(){}
	public function search(){}
	public function view(){}

	public static function renderTabs( CMF_Hydrogen_Environment_Abstract $env, $current = 0 ){
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './member/' );
		$env->getModules()->callHook( "Member", "registerTabs", $tabs/*, $data*/ );	//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}
}
