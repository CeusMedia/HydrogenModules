<?php
class Controller_Index extends CMF_Hydrogen_Controller{
	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL ){
		$this->addData( 'isInside', $this->env->getSession()->has( 'userId' ) );
	}
}
?>
