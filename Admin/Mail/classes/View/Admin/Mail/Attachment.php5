<?php
class View_Admin_Mail_Attachment extends CMF_Hydrogen_View{

	public static function ___onRegisterTab( $env, $context, $module, $data ){
		$words	= (object) $env->getLanguage()->getWords( 'admin/mail/attachment' );				//  load words
		$context->registerTab( '', $words->tabs['index'], 0 );										//  register main tab
		$context->registerTab( 'add', $words->tabs['add'], 1 );									//  register main tab
		$context->registerTab( 'upload', $words->tabs['upload'], 2 );									//  register main tab
	}

	public function add(){}

	public function edit(){}

	public function index(){}

	public static function renderTabs( CMF_Hydrogen_Environment_Abstract $env, $current = 0 ){
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './admin/mail/attachment/' );
		$env->getModules()->callHook( "AdminMailAttachment", "registerTabs", $tabs/*, $data*/ );	//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}

	public function upload(){}
}
?>
