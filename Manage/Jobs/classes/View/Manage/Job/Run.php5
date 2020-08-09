<?php
class View_Manage_Job_Run extends CMF_Hydrogen_View
{
	public function index()
	{
	}

	public function view()
	{
	}

	protected function __onInit()
	{
		$this->env->getPage()->addThemeStyle( 'module.manage.jobs.css' );
		$this->env->getPage()->loadLocalScript( 'module.manage.jobs.js' );
	}
}
