<?php
class View_Admin_Module extends CMF_Hydrogen_View
{
	public function index()
	{
	}

	public function view()
	{
	}

	protected function __onInit()
	{
		$this->env->page->addThemeStyle( 'module.admin.module.css' );
	}
}
