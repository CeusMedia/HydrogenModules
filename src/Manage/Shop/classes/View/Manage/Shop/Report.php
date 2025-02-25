<?php

use CeusMedia\HydrogenFramework\Environment;

class View_Manage_Shop_Report extends View_Manage_Shop
{
	public function index(): void
	{
		$uriGoogleAPI	= "https://www.google.com/jsapi";
		$this->env->getPage()->addJavaScript( $uriGoogleAPI );
	}
}
