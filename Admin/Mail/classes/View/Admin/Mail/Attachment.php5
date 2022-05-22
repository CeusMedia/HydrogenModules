<?php

use CeusMedia\HydrogenFramework\Environment;

class View_Admin_Mail_Attachment extends CMF_Hydrogen_View
{
	public static function renderTabs( Environment $env, $current = 0 )
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './admin/mail/attachment/' );
		$env->getModules()->callHook( "AdminMailAttachment", "registerTabs", $tabs/*, $data*/ );	//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}

	public function add()
	{
	}

	public function edit()
	{
	}

	public function index()
	{
	}

	public function upload()
	{
	}

	protected function __onInit()
	{
		$this->env->getPage()->addCommonStyle( 'module.admin.mail.css' );
	}
}
