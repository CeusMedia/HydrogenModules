<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request;
use CeusMedia\Common\UI\Image\Captcha as ImageCaptcha;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger;

class Controller_Share extends Controller
{
	protected Request $request;
	protected Dictionary $session;
	protected Messenger $messenger;
	protected Logic_Share $logic;

	public function index( string $uuid ): void
	{
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
		$captcha->unique		= TRUE;
		$captcha->useDigits		= TRUE;
		$captcha->useLarges		= FALSE;
		$captcha->useSmalls		= TRUE;
		$captcha->length		= 6;
		$captcha->background	= [247, 247, 247];
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

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logic		= Logic_Share::getInstance( $this->env );
	}
}
