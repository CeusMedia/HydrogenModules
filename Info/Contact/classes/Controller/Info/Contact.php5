<?php
class Controller_Info_Contact extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->moduleConfig	= $this->env->getConfig()->getAll( "module.info_contact.", TRUE );
		$this->useCaptcha		= $this->moduleConfig->get( 'captcha.enable' );
		$this->useNewsletter	= $this->moduleConfig->get( 'newsletter.enable' );
		$this->addData( 'useCaptcha', $this->useCaptcha );
		$this->addData( 'useNewsletter', $this->useNewsletter );
		$this->addData( 'useHoneypot', $this->moduleConfig->get( 'honeypot.enable' ) );
	}

	public function ajaxForm(){
		$request	= $this->env->getRequest();
		$message	= '';
		$data		= NULL;
		if( !$request->isAjax() )
			$message	= "Access granted for AJAX requests, only.";
		else if( !$request->isPost() )
			$message	= "Access granted for POST requests, only.";
		else{
			try{
				$logic		= Logic_Mail::getInstance( $this->env );
				$mail		= new Mail_Info_Contact_Form( $this->env, $request->getAll() );
				$receiver	= (object) array( 'email' => $this->moduleConfig->get( 'mail.receiver' ) );
				$logic->handleMail( $mail, $receiver, 'de' );
				$data		= TRUE;
			}
			catch( Exception $e ){
				$message	= $e->getMessage();
			}
		}
		header( 'Content-Type: application/json' );
		if( $message ){
			print( json_encode( array(
				'status'	=> "error",
				'message'	=> $message,
			) ) );
		}
		else{
			print( json_encode( array(
				'status'	=> "data",
				'data'		=> $data,
			) ) );
		}
		exit;
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
					$logic		= Logic_Mail::getInstance( $this->env );
					$mail		= new Mail_Info_Contact( $this->env, $data );
					$receiver	= (object) array( 'email' => $this->moduleConfig->get( 'mail.receiver' ) );
					$logic->handleMail( $mail, $receiver, 'de' );
					$messenger->noteSuccess( $words->msgSuccess );
					$this->restart( NULL, TRUE );

				//	@todo handle newsletter registration
				}
				catch( Exception $e ){
					$messenger->noteFailure( $e->getMessage() );
					$this->restart( NULL, TRUE );
				}
			}
		}

		$path	= "./info/contact";
		if( $this->env->getModules()->has( 'Info_Pages' ) ){
			$model	= new Model_Page( $this->env );
			$page	= $model->getByIndex( 'controller', 'Info_Contact' );
			$path	= "./".$page->identifier;
		}

		if( $this->useNewsletter ){
			$topics		= array();
			if( $this->env->getModules()->has( 'Resource_Newsletter' ) ){
	//			$model	= new Model_Newsletter_Group( $this->env );
	//			$conditions	= array( 'status' => '', 'type' => '' );
	//			$orders		= array( 'title' => 'ASC' );
	//			$topics		= $model->getAll( $conditions, $orders );
			}
			$this->addData( 'newsletterTopics', $topics );
		}

		if( $this->useCaptcha ){
			$captcha	= new UI_Image_Captcha();
			$captcha->useUnique	= TRUE;
			$filePath	= $this->moduleConfig->get( 'captcha.path' );
			$filePath	= $filePath ? $filePath : $this->env->getConfig()->get( 'path.images' );
			$filePath	= $filePath ? $filePath : 'tmp/';
			$filePath	= $filePath."/captcha.jpg";
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
