<?php
class View_Manage_Customer_Rating extends CMF_Hydrogen_View{
	public function add(){}
	public function index(){}
	public function rate(){}
	public function view(){}

	public static function ___onRegisterTab( $env, $context, $context, $data ){
		View_Manage_Customer::registerTab( 'rating/'.$data['customerId'], '+Bewertungen', 7 );
	}
}
?>
