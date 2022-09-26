<?php

use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\HydrogenFramework\View;

class View_Admin_Mail_Template extends View
{
	public function add()
	{
		$template	= $this->getData( 'template' );
		$defaults	= array(
			'plain'	=> 'default.txt',
			'html'	=> 'default.html',
			'css'	=> 'default.css',
		);
		$defaultsPath	= 'admin/mail/template/';
		foreach( $defaults as $key => $value ){
			if( empty( $template->{$key} ) ){
				$templateFile	= $this->getTemplateUriFromFile( $defaultsPath.$value );
				$template->{$key}	= FileReader::load( $templateFile );
			}
		}
	}

	public function edit()
	{
		$template	= $this->getData( 'template' );
		$script	= 'ModuleAdminMail.TemplateEditor.init('.$template->mailTemplateId.');';
		$this->env->getPage()->js->addScriptOnReady( $script );
	}

	public function import()
	{
	}

	public function index()
	{
	}

	public function remove()
	{
	}

	protected function __onInit()
	{
		$this->env->getPage()->addCommonStyle( 'module.admin.mail.css' );
		$this->env->getPage()->js->addModuleFile( 'module.admin.mail.js' );
	}
}
