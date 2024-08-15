<?php

use CeusMedia\HydrogenFramework\View;

class View_Admin_Log_Exception extends View
{
	/**
	 *	@return		void
	 */
	public function index(): void
	{
		$script	= 'ModuleAdminLogException.Index.init();';
		$this->env->getPage()->js->addScriptOnReady( $script );
	}

	/**
	 *	@return		void
	 */
	public function view(): void
	{
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->env->getPage()->addCommonStyle( 'module.admin.log.exception.css' );
		$this->env->getPage()->js->addModuleFile( 'module.admin.log.exception.js' );
	}
}
