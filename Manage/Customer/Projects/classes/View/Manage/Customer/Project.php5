<?php
class View_Manage_Customer_Project extends CMF_Hydrogen_View{
	public function index(){}

	public static function ___onRegisterTab( $env, $context, $context, $data ){
		View_Manage_Customer::registerTab( 'project/'.$data['customerId'], '+Projekte', 5 );
	}
}
?>