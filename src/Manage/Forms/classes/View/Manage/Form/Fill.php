<?php

use CeusMedia\HydrogenFramework\View;

class View_Manage_Form_Fill extends View
{
	public function index(): void
	{
	}

	public function view(): void
	{
	}

	/**
	 *	Loads CSS file of module.
	 *	@return void
	 */
	protected function __onInit(): void
	{
		$this->env->getPage()->addThemeStyle( 'module.manage.forms.css' );
	}
}
