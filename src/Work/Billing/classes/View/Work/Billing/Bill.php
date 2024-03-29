<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Work_Billing_Bill extends View
{
	public static function renderTabs( Environment $env, string $billId, $current = 0 ): string
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './work/billing/bill/' );
		$data	= ['billId' => $billId];
		$env->getModules()->callHookWithPayload( "WorkBilling/Bill", "registerTabs", $tabs, $data );
		return $tabs->renderTabs( $current );
	}

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
	}
}
