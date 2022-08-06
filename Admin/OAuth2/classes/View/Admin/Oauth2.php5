<?php

use CeusMedia\HydrogenFramework\View;

class View_Admin_Oauth2 extends View
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
		$this->env->getPage()->addCommonStyle( 'module.admin.oauth2.css', 8 );
		$this->env->getPage()->loadLocalScript( 'module.admin.oauth2.js', 8 );
	}
}
