<?php

use CeusMedia\HydrogenFramework\View;

class View_Admin_Module extends View
{
	public function index()
	{
	}

	public function view()
	{
	}

	protected function __onInit(): void
	{
		$this->env->page->addThemeStyle( 'module.admin.module.css' );
	}
}
