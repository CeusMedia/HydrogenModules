<?php
class View_Work_Billing_Bill extends CMF_Hydrogen_View
{
	public function __onInit()
	{
	}

	public function add()
	{
	}

	public function edit()
	{
	}

	public function index()
	{
	}

	public static function renderTabs( CMF_Hydrogen_Environment $env, $billId, $current = 0 ): string
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './work/billing/bill/' );
		$data	= array( 'billId' => $billId );
		$env->getModules()->callHook( "WorkBilling/Bill", "registerTabs", $tabs, $data );
		return $tabs->renderTabs( $current );
	}
}
