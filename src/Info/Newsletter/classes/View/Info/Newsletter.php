<?php

use CeusMedia\HydrogenFramework\View;

class View_Info_Newsletter extends View
{
	/**
	 *	@return		void
	 */
	public function index(): void
	{
		$script	= 'Module_Info_Newletter_Form.init();';
		$this->env->getPage()->js->addScriptOnReady( $script );
		$this->env->getPage()->js->addModuleFile( 'module.info.newsletter.js' );
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
	public function register(): void
	{
	}

	/**
	 *	@return		void
	 */
	public function unregister(): void
	{
	}

	/**
	 *	@return		void
	 */
	public function edit(): void
	{
	}

	/**
	 *	@return		void
	 */
	public function preview(): void
	{
	}
}
