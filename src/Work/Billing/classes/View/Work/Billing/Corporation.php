<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Work_Billing_Corporation extends View
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

	public static function renderTabs( Environment $env, string $corporationId, $current = 0 ): string
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './work/billing/corporation/' );
		$data	= ['corporationId' => $corporationId];
		$env->getModules()->callHookWithPayload( "WorkBilling/Corporation", "registerTabs", $tabs, $data );
		return $tabs->renderTabs( $current );
	}
}
