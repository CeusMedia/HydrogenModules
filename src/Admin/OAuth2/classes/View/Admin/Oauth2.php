<?php

use CeusMedia\HydrogenFramework\View;

class View_Admin_Oauth2 extends View
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
		$this->env->getPage()->addCommonStyle( 'module.admin.oauth2.css', 8 );
		$this->env->getPage()->loadLocalScript( 'module.admin.oauth2.js', 8 );
	}
}
