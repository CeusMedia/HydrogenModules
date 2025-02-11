<?php

use CeusMedia\HydrogenFramework\View;

class View_Manage_Job_Schedule extends View
{
	public function add(): void
	{
	}

	public function edit(): void
	{
	}

	public function index(): void
	{
	}

	protected function __onInit(): void
	{
		$this->env->getPage()->addThemeStyle( 'module.manage.jobs.css' );
		$this->env->getPage()->loadLocalScript( 'module.manage.jobs.js' );
	}
}
