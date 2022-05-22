<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Work_Billing_Corporation extends View
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

	public static function renderTabs( Environment $env, $corporationId, $current = 0 ): string
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './work/billing/corporation/' );
		$data	= array( 'corporationId' => $corporationId );
		$env->getModules()->callHook( "WorkBilling/Corporation", "registerTabs", $tabs, $data );
		return $tabs->renderTabs( $current );
	}
}
