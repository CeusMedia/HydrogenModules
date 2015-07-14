<?php
class Controller_Info_Contact extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->moduleConfig	= $this->env->getConfig()->getAll( "module.info_contact.", TRUE );
		$this->useCaptcha	= $this->moduleConfig->get( 'captcha.enable' );
		$this->addData( 'useCaptcha', $this->useCaptcha );
		$this->addData( 'useHoneypot', $this->moduleConfig->get( 'honeypot.enable' ) );
	}

	public function index(){
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'index' );

		if( $request->has( 'save' ) ){
			if( !trim( $request->get( 'name' ) ) )
				$messenger->noteError( $words->msgErrorNameMissing );
			if( !trim( $request->get( 'email' ) ) )
				$messenger->noteError( $words->msgErrorEmailMissing );
			if( !trim( $request->get( 'subject' ) ) )
				$messenger->noteError( $words->msgErrorSubjectMissing );
			if( !trim( $request->get( 'message' ) ) )
				$messenger->noteError( $words->msgErrorMessageMissing );
			if( trim( $request->get( 'trap' ) ) )
				$messenger->noteError( $words->msgErrorAccessDenied );
			if( $this->useCaptcha ){
				$word	= $this->env->getSession()->get( 'captcha' );
				if( $request->get( 'captcha' ) !== $word )
					$messenger->noteError( $words->msgErrorCaptchaFailed );
			}
			if( !$messenger->gotError() ){
				$data	= $request->getAll();
				try{
					$logic		= new Logic_Mail( $this->env );
					$mail		= new Mail_Info_Contact( $this->env, $data );
					$receiver	= (object) array( 'email' => $this->moduleConfig->get( 'mail.receiver' ) );
					$logic->handleMail( $mail, $receiver, 'de' );
					$messenger->noteSuccess( $words->msgSuccess );
					$this->restart( NULL, TRUE );
				}
				catch( Exception $e ){
					die( $e->getMessage() );
				}
			}
		}

		$path	= "./info/contact";
		if( $this->env->getModules()->has( 'Info_Pages' ) ){
			$model	= new Model_Page( $this->env );
			$page	= $model->getByIndex( 'module', 'Info_Contact' );
			$path	= "./".$page->identifier;
		}

		if( $this->useCaptcha ){
			$captcha	= new UI_Image_Captcha();
			$captcha->useUnique	= TRUE;
			$filePath	= $this->moduleConfig->get( 'captcha.path' )."/captcha.jpg";
			if( $this->moduleConfig->get( 'captcha.strength' ) == 'hard' ){
					$captcha->useDigits	= TRUE;
					$captcha->useLarge	= TRUE;
			}
			$word	= $captcha->generateWord();
			$this->env->getSession()->set( 'captcha', $word );
			$this->addData( 'captchaWord', $word );
			$this->addData( 'captchaFilePath', $filePath );
		}
		$this->addData( 'formPath', $path );
		$this->addData( 'name', $request->get( 'name' ) );
		$this->addData( 'email', $request->get( 'email' ) );
		$this->addData( 'subject', $request->get( 'subject' ) );
		$this->addData( 'message', $request->get( 'message' ) );
	}
}
?>
