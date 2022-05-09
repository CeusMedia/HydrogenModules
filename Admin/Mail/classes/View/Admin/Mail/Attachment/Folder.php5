<?php
class View_Admin_Mail_Attachment_Folder extends CMF_Hydrogen_View
{
	public function __onInit()
	{
		$this->env->getPage()->addCommonStyle( 'module.admin.mail.css' );
	}

	public static function renderTabs( CMF_Hydrogen_Environment $env )
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './admin/mail/attachment/' );
		$env->getModules()->callHook( "AdminMailAttachment", "registerTabs", $tabs/*, $data*/ );	//  call tabs to be registered
		return $tabs->renderTabs( 'folder' );
	}

	public function index()
	{
	}
}
