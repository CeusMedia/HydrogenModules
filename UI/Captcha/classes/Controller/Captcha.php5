<?php
class Controller_Captcha extends CMF_Hydrogen_Controller{

	public function image(){
		$helper	= new View_Helper_Captcha( $this->env );
		$helper->setFormat( View_Helper_Captcha::FORMAT_RAW );
		$image	= $helper->render();
		header( 'Content-Type: image/jpg' );
		print $image;
		exit;
	}
}
