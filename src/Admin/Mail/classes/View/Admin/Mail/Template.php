<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\HydrogenFramework\View;

class View_Admin_Mail_Template extends View
{
	public function add(): void
	{
		$defaultsPath	= 'admin/mail/template/';

		/** @var Entity_Mail_Template $template */
		$template	= $this->getData( 'template' );
		$template->plain	= $this->loadTemplateFile( $defaultsPath.'default.txt', [], FALSE );
		$template->html		= $this->loadTemplateFile( $defaultsPath.'default.html', [], FALSE );
		$template->css		= $this->loadTemplateFile( $defaultsPath.'default.css', [], FALSE );
	}

	public function edit(): void
	{
		$template	= $this->getData( 'template' );
		$script		= 'ModuleAdminMail.TemplateEditor.init('.$template->mailTemplateId.');';
		$this->env->getPage()->js->addScriptOnReady( $script );
	}

	public function import(): void
	{
	}

	public function index(): void
	{
	}

	public function remove(): void
	{
	}

	protected function __onInit(): void
	{
		$this->env->getPage()->addCommonStyle( 'module.admin.mail.css' );
		$this->env->getPage()->js->addModuleFile( 'module.admin.mail.js' );
	}
}
