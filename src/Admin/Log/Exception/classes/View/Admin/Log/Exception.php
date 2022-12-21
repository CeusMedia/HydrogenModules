<?php

use CeusMedia\HydrogenFramework\View;

class View_Admin_Log_Exception extends View
{
	public function index()
	{
		$script	= 'ModuleAdminLogException.Index.init();';
		$this->env->getPage()->js->addScriptOnReady( $script );
	}

	public function view()
	{

	}

	protected function __onInit(): void
	{
		$this->env->getPage()->addCommonStyle( 'module.admin.log.exception.css' );
		$this->env->getPage()->js->addModuleFile( 'module.admin.log.exception.js' );
	}
}
