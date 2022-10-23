<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Manage_Customer_Project extends View
{
	public function index()
	{
	}

	public static function ___onRegisterTab( Environment $env, $context, $module, $data )
	{
		View_Manage_Customer::registerTab( 'project/'.$data['customerId'], '+Projekte', 5 );
	}
}
