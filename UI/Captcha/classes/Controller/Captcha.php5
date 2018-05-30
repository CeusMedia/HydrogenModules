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

	public function test(){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$messenger	= $this->env->getMessenger();
		if( $request->isPost() ){
			$code	= $request->get( 'captcha' );
			if( View_Helper_Captcha::checkCaptcha( $this->env, $code ) )
				$messenger->noteSuccess( 'Der CAPTCHA-Code war richtig.' );
			else
				$messenger->noteError( 'Der CAPTCHA-Code war nicht richtig.' );
		}
	}
}
