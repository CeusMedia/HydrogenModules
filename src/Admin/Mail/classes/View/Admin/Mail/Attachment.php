<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Admin_Mail_Attachment extends View
{
	public static function renderTabs( Environment $env, $current = 0 ): string
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './admin/mail/attachment/' );
		$env->getModules()->callHook( "AdminMailAttachment", "registerTabs", $tabs/*, $data*/ );	//  call tabs to be registered
		return $tabs->renderTabs( $current );
	}

	public function add(): void
	{
	}

	public function edit(): void
	{
	}

	public function index(): void
	{
	}

	public function upload(): void
	{
	}

	protected function __onInit(): void
	{
		$this->env->getPage()->addCommonStyle( 'module.admin.mail.css' );
	}
}
