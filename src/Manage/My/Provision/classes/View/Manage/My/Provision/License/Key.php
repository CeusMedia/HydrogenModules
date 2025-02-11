<?php
class View_Manage_My_Provision_License_Key extends View_Manage_My_Provision_License
{
	public function edit(): void
	{
	}

	public function index(): void
	{
	}

	public function view(): void
	{
	}

	protected function __onInit(): void
	{
		$this->env->getPage()->addThemeStyle( 'module.manage.my.provision.css' );
	}
}
