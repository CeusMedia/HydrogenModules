<?php
class View_Manage_Shop_Report extends View_Manage_Shop{

	public function index(){
		$uriGoogleAPI	= "https://www.google.com/jsapi";
		$this->env->getPage()->addJavaScript( $uriGoogleAPI );
	}

}
?>
