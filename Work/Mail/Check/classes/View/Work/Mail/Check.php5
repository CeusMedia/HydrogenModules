<?php
class View_Work_Mail_Check extends CMF_Hydrogen_View{

	public static function ___onRegisterTab( $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'work/mail/check' );								//  load words
		$context->registerTab( '', $words->tabs['index'], 0 );												//  register main tab
		$context->registerTab( 'group', $words->tabs['group'], 1 );											//  register main tab
		$context->registerTab( 'import', $words->tabs['import'], 2 );										//  register main tab
		$context->registerTab( 'export', $words->tabs['export'], 3 );										//  register main tab
	}

	public function add(){}

	public function export(){}

	public function group(){}

	public function index(){}

	public function import(){}

	public function status(){}

	public static function renderTabs( CMF_Hydrogen_Environment_Abstract $env, $current = 0 ){
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './work/mail/check/' );
		$env->getModules()->callHook( "WorkMailCheck", "registerTabs", $tabs/*, $data*/ );	//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}
}
