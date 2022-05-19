<?php
class View_Catalog_Provision_Product extends CMF_Hydrogen_View
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

	protected function __onInit()
	{
		$this->env->getPage()->addCommonStyle( 'module.catalog.provision.css' );
	}
}
