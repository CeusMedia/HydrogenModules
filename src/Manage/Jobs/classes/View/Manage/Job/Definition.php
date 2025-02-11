<?php

use CeusMedia\HydrogenFramework\View;

class View_Manage_Job_Definition extends View
{
	public function index(): void
	{
	}

	public function view(): void
	{
	}

	protected function __onInit(): void
	{
		$this->env->getPage()->addThemeStyle( 'module.manage.jobs.css' );
		$this->env->getPage()->loadLocalScript( 'module.manage.jobs.js' );
	}
}
