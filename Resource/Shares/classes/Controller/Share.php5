<?php

use CeusMedia\Common\UI\Image\Captcha as ImageCaptcha;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Share extends Controller{

	protected $request;
	protected $session;
	protected $messenger;

	protected function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= Logic_Share::getInstance( $this->env );
	}

	public function index( $uuid ){
		if( $this->request->getMethod()->isPost() ){
			$captcha	= $this->request->get( 'captcha' );
			if( $captcha !== $this->session->get( 'captcha' ) ){
				$this->messenger->noteError( 'Der Sicherheitscode wurde nicht korrekt eingegeben. Bitte noch einmal!' );
				$this->restart( $uuid, TRUE );
			}
			$share	= $this->logic->getByUuid( $uuid );
			if( !$share ){
				$this->messenger->noteError( 'Für diesen Share-Code existiert kein Dokument.' );
				$this->restart( $uuid, TRUE );
			}
			$this->restart( 'file/'.$share->path );
		}
		$captcha	= new ImageCaptcha();
		$captcha->useUnique		= TRUE;
		$captcha->useDigits		= TRUE;
		$captcha->useLarges		= FALSE;
		$captcha->useSmalls		= TRUE;
		$captcha->length		= 6;
		$captcha->background	= array( 247, 247, 247 );
		$captcha->width			= 160;
		$captcha->height		= 60;
		$captcha->fontSize		= 16;
		$captcha->offsetX		= 8;
		$captcha->offsetY		= 8;
		$captcha->font			= "./themes/common/font/Tahoma.ttf";
		$word	= $captcha->generateWord();
		$image	= $captcha->generateImage( $word );
		$this->env->getSession()->set( 'captcha', $word );
		$this->addData( 'captchaLength', $captcha->length );
		$this->addData( 'captchaImage', $image );
		$this->addData( 'show', FALSE );
		$this->addData( 'uuid', $uuid );

	}
}
