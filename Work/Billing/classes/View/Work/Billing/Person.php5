<?php

use CeusMedia\HydrogenFramework\Environment;

class View_Work_Billing_Person extends CMF_Hydrogen_View
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

	public static function renderTabs( Environment $env, $personId, $current = 0 ): string
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './work/billing/person/' );
		$data	= array( 'personId' => $personId );
		$env->getModules()->callHook( "WorkBilling/Person", "registerTabs", $tabs, $data );
		return $tabs->renderTabs( $current );
	}
}
