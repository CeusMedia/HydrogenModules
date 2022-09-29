<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Admin_Mail_Attachment_Folder extends View
{
	public static function renderTabs( Environment $env ): string
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './admin/mail/attachment/' );
		$env->getModules()->callHook( "AdminMailAttachment", "registerTabs", $tabs/*, $data*/ );	//  call tabs to be registered
		return $tabs->renderTabs( 'folder' );
	}

	public function index()
	{
	}

	protected function __onInit()
	{
		$this->env->getPage()->addCommonStyle( 'module.admin.mail.css' );
	}
}
