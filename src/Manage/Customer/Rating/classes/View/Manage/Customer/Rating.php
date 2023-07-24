<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Manage_Customer_Rating extends View
{
	public function add()
	{
	}

	public function index()
	{
	}

	public function rate()
	{
	}

	public function view()
	{
	}

	public static function ___onRegisterTab( Environment $env, $context, $module, $data )
	{
		View_Manage_Customer::registerTab( 'rating/'.$data['customerId'], '+Bewertungen', 7 );
	}
}
