<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Captcha extends Controller
{
	public function image(): void
	{
		$helper	= new View_Helper_Captcha( $this->env );
		$helper->setFormat( View_Helper_Captcha::FORMAT_RAW );
		$image	= $helper->render();
		header( 'Content-Type: image/jpg' );
		print $image;
		exit;
	}

	public function test(): void
	{
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$messenger	= $this->env->getMessenger();
		if( $request->getMethod()->isPost() ){
			$code	= $request->get( 'captcha' );
			if( View_Helper_Captcha::checkCaptcha( $this->env, $code ) )
				$messenger->noteSuccess( 'Der CAPTCHA-Code war richtig.' );
			else
				$messenger->noteError( 'Der CAPTCHA-Code war nicht richtig.' );
		}
	}
}
