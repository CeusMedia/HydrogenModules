<?php
class Controller_Info_Contact extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->request			= $this->env->getRequest();
		$this->messenger		= $this->env->getMessenger();
		$this->moduleConfig		= $this->env->getConfig()->getAll( "module.info_contact.", TRUE );
		$this->useCaptcha		= NULL;

		if( $this->moduleConfig->get( 'captcha.enable' ) ){
			$configCaptcha	= $this->env->getConfig()->getAll( 'module.ui_captcha.', TRUE );
			if( !$configCaptcha->get( 'active' ) )
				$this->messenger->noteFailure( 'Module "UI_Captcha" needs to be installed to use CAPTCHA.' );
			else
				$this->useCaptcha	= $configCaptcha->get( 'mode' );
		}
		$this->useNewsletter	= $this->moduleConfig->get( 'newsletter.enable' );
		$this->addData( 'useCaptcha', $this->useCaptcha );
		$this->addData( 'useNewsletter', $this->useNewsletter );
		$this->addData( 'useHoneypot', $this->moduleConfig->get( 'honeypot.enable' ) );
	}

	public function ajaxForm(){
		$message	= '';
		$data		= NULL;
		if( !$this->request->isAjax() )
			$message	= "Access granted for AJAX requests, only.";
		else if( !$this->request->isPost() )
			$message	= "Access granted for POST requests, only.";
		else{
			try{
				$logic		= Logic_Mail::getInstance( $this->env );
				$mail		= new Mail_Info_Contact_Form( $this->env, $this->request->getAll() );
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
		$words			= (object) $this->getWords( 'index' );

		if( $this->request->has( 'save' ) ){
			$valid	= TRUE;
			if( !strlen( trim( $this->request->get( 'name' ) ) ) ){
				$this->messenger->noteError( $words->msgErrorNameMissing );
				$valid	= FALSE;
			}
			if( !strlen( trim( $this->request->get( 'email' ) ) ) ){
				$this->messenger->noteError( $words->msgErrorEmailMissing );
				$valid	= FALSE;
			}
			if( !strlen( trim( $this->request->get( 'subject' ) ) ) ){
				$this->messenger->noteError( $words->msgErrorSubjectMissing );
				$valid	= FALSE;
			}
			if( !strlen( trim( $this->request->get( 'message' ) ) ) ){
				$this->messenger->noteError( $words->msgErrorMessageMissing );
				$valid	= FALSE;
			}
			if( strlen( trim( $this->request->get( 'trap' ) ) ) ){
				$this->messenger->noteError( $words->msgErrorAccessDenied );
				$valid	= FALSE;
			}
			if( $this->useCaptcha ){
				$captchaWord	= $this->request->get( 'captcha' );
				if( !View_Helper_Captcha::checkCaptcha( $this->env, $captchaWord ) ){
					$this->messenger->noteError( $words->msgErrorCaptchaFailed );
					$valid	= FALSE;
				}
			}
			if( !$valid )
				$this->restart( NULL, TRUE );
			$data	= $this->request->getAll();
			try{
				$logic		= Logic_Mail::getInstance( $this->env );
				$mail		= new Mail_Info_Contact( $this->env, $data );
				$receiver	= (object) array( 'email' => $this->moduleConfig->get( 'mail.receiver' ) );
				$logic->handleMail( $mail, $receiver, 'de' );
				$this->messenger->noteSuccess( $words->msgSuccess );

				//  --  NEWSLETTER  FORWARDING  --  //
				if( $this->useNewsletter && $this->request->has( 'newsletter' ) ){
					if( $this->env->getModules()->has( 'Resource_Newsletter' ) ){
						$path	= 'info/newsletter';
						if( $this->env->getModules()->has( 'Info_Pages' ) ){
							$logicPage	= $this->env->getLogic()->page;
							$page	= $logicPage->getPageFromControllerAction( 'Info_Newsletter', 'index', FALSE );
							if( !$page )
								$page	= $logicPage->getPageFromController( 'Info_Newsletter', FALSE );
							if( $page )
								$path	= $page->fullpath;
						}
						$name	= trim( $this->request->get( 'name' ) );
						$parts	= preg_split( '/\s+/', $name.' ' );
						$path	= $path.'?'.http_build_query( array(
							'firstname'		=> $parts[0],
							'surname'		=> $parts[1],
							'email'			=> $this->request->get( 'email' ),
							'groups'		=> $this->request->get( 'topics' ),
						), '', '&' );
						$this->restart( $path, FALSE );
					}
				}
				$this->restart( NULL, TRUE );

			//	@todo handle newsletter registration
			}
			catch( Exception $e ){
				$this->messenger->noteFailure( $e->getMessage() );
				$this->restart( NULL, TRUE );
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
				$model	= new Model_Newsletter_Group( $this->env );
				$conditions	= array(
					'status'	=> Model_Newsletter_Group::STATUS_USABLE,
					'type'		=> array(
						Model_Newsletter_Group::TYPE_DEFAULT,
						Model_Newsletter_Group::TYPE_AUTOMATIC
					) );
				$orders		= array( 'title' => 'ASC' );
				$topics		= $model->getAll( $conditions, $orders );
			}
			$this->addData( 'newsletterTopics', $topics );
		}

		if( $this->useCaptcha === "default" ){
			$this->addData( 'captchaLength', $this->moduleConfig->get( 'captcha.length' ) );
			$this->addData( 'captchaStrength', $this->moduleConfig->get( 'captcha.strength' ) );
		}
		$this->addData( 'formPath', $path );
		$this->addData( 'name', $this->request->get( 'name' ) );
		$this->addData( 'email', $this->request->get( 'email' ) );
		$this->addData( 'subject', $this->request->get( 'subject' ) );
		$this->addData( 'message', $this->request->get( 'message' ) );
	}
}
?>
