<?php

use CeusMedia\HydrogenFramework\View;

class View_Catalog_Provision_Product extends View
{
	public function index()
	{
	}

	public function license()
	{
	}

	public function view()
	{
	}

	protected function __onInit(): void
	{
		$this->env->getPage()->addCommonStyle( 'module.catalog.provision.css' );
	}
}
