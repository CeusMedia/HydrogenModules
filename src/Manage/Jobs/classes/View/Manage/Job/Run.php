<?php

use CeusMedia\HydrogenFramework\View;

class View_Manage_Job_Run extends View
{
	public function index()
	{
	}

	public function view()
	{
	}

	protected function __onInit(): void
	{
		$this->env->getPage()->addThemeStyle( 'module.manage.jobs.css' );
		$this->env->getPage()->loadLocalScript( 'module.manage.jobs.js' );
	}
}
