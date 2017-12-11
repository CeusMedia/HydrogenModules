<?php
class View_Work_Mail_Group extends CMF_Hydrogen_View{

	public function add(){}
	public function edit(){}
	public function index(){}

	public static function ___onRegisterTab( $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'work/mail/group' );					//  load words
		$context->registerTab( '', $words->tabs['group'], 0 );									//  register main tab
	//	$context->registerTab( 'member', $words->tabs['members'], 1 );							//  register main tab
		$context->registerTab( 'server', $words->tabs['servers'], 2 );							//  register main tab
		$context->registerTab( 'role', $words->tabs['roles'], 3 );								//  register main tab
	}

	public static function renderTabs( CMF_Hydrogen_Environment_Abstract $env, $current = 0 ){
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './work/mail/group/' );
		$env->getModules()->callHook( "WorkMailGroup", "registerTabs", $tabs/*, $data*/ );		//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}
}
