<?php
class View_Manage_Job_Schedule extends CMF_Hydrogen_View
{
	public function add()
	{
	}

	public function edit()
	{
	}

	public function index()
	{
	}

	protected function __onInit()
	{
		$this->env->getPage()->addThemeStyle( 'module.manage.jobs.css' );
		$this->env->getPage()->loadLocalScript( 'module.manage.jobs.js' );
	}
}
