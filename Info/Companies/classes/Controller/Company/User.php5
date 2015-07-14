<?php
class Controller_Company_User extends CMF_Hydrogen_Controller{

	public function __onInit(){}

	public function index( $userId = NULL ){
		if( $userId !== NULL && strlen( trim( $userId ) ) && (int) $userId > 0 ){
			$this->redirect( 'company/user', 'view', array( $userId ) );
		}
	}

	public function view( $userId ){
		
	}
}
?>
