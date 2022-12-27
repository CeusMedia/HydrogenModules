<?php

use CeusMedia\HydrogenFramework\View;

class View_Catalog_Provision_Product extends View
{
	public function index(): void
	{
	}

	public function license(): void
	{
	}

	public function view(): void
	{
	}

	protected function __onInit(): void
	{
		$this->env->getPage()->addCommonStyle( 'module.catalog.provision.css' );
	}
}
