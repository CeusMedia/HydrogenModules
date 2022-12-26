<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\HydrogenFramework\View;

class View_Admin_Mail_Template extends View
{
	public function add(): void
	{
		$template	= $this->getData( 'template' );
		$defaults	= [
			'plain'	=> 'default.txt',
			'html'	=> 'default.html',
			'css'	=> 'default.css',
		];
		$defaultsPath	= 'admin/mail/template/';
		foreach( $defaults as $key => $value ){
			if( empty( $template->{$key} ) ){
				$templateFile	= $this->getTemplateUriFromFile( $defaultsPath.$value );
				$template->{$key}	= FileReader::load( $templateFile );
			}
		}
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
