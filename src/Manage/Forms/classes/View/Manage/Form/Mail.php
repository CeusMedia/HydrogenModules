<?php

use CeusMedia\HydrogenFramework\View;

class View_Manage_Form_Mail extends View
{
	public function add(){}

	public function edit(){}

	public function index(){}

	public function view(){}

	protected function __onInit(): void
	{
		$this->env->getPage()->addThemeStyle( 'module.manage.forms.css' );
	}
}
