<?php

use CeusMedia\HydrogenFramework\Environment;

class View_Manage_Customer_Project extends CMF_Hydrogen_View{
	public function index(){}

	public static function ___onRegisterTab( Environment $env, $context, $module, $data ){
		View_Manage_Customer::registerTab( 'project/'.$data['customerId'], '+Projekte', 5 );
	}
}
?>
