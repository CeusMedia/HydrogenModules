<?php

use CeusMedia\HydrogenFramework\View;

class View_Manage_Form_Fill extends View
{
	/**
	 *	Will automatically load template manage/form/fill/index and return to app flow.
	 *	@return		void
	 */
	public function index(): void
	{
	}

	/**
	 *	Will automatically load template manage/form/fill/view and return to app flow.
	 *	@return		void
	 */
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
