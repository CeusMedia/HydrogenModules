<?php
class Controller_Admin_Payment_Mangopay extends CMF_Hydrogen_Controller{
	public function index(){
		$this->restart( 'client', TRUE );
	}
}
