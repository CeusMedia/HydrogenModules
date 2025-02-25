<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Manage_Shop_Shipping extends View
{
	public function index()
	{
		$this->env->getPage()->addCommonStyle( 'module.manage.shop.css' );
		$this->env->getPage()->js->addModuleFile( 'module.manage.shop.js' );
	}

	protected function __onInit(): void
	{
	}
}
