<?php

use CeusMedia\HydrogenFramework\View;

class View_Manage_Form_Mail extends View
{
	/**
	 *	Will automatically load template manage/form/mail/add and return to app flow.
	 *	@return		void
	 */
	public function add(): void
	{
	}

	/**
	 *	Will automatically load template manage/form/mail/edit and return to app flow.
	 *	@return		void
	 */
	public function edit(): void
	{
	}

	/**
	 *	Will automatically load template manage/form/mail/index and return to app flow.
	 *	@return		void
	 */
	public function index(): void
	{
	}

	/**
	 *	Will automatically load template manage/form/mail/view and return to app flow.
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
