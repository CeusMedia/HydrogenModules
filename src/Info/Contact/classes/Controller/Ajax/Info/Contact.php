<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Info_Contact extends AjaxController
{
	protected $messenger;
	protected $moduleConfig;

	protected $useCaptcha;
	protected $useCsrf;
	protected $useHoneypot;

	/**
	 *	@todo		add support for captcha and CSRF
	 */
	public function form()
	{
		$message	= '';
		$data		= NULL;
		if( !$this->request->getMethod()->isPost() )
			$this->respondError( 0, 'Access granted for POST requests, only.' );
		else{
			$message	= "";
			try{
				$logic		= Logic_Mail::getInstance( $this->env );
				$mail		= new Mail_Info_Contact_Form( $this->env, $this->request->getAll() );
				$receiver	= (object) ['email' => $this->moduleConfig->get( 'mail.receiver' )];
				$logic->handleMail( $mail, $receiver, 'de' );
				$this->respondData( TRUE );
			}
			catch( Exception $e ){
				$this->env->getLog()->logException( $e );
				$this->respondError( 0, 'Access granted for POST requests, only.' );
			}
		}
	}

	protected function __onInit(): void
	{
		$this->moduleConfig		= $this->env->getConfig()->getAll( "module.info_contact.", TRUE );

		$this->useCaptcha		= NULL;
		$this->useCsrf			= FALSE;

		if( $this->moduleConfig->get( 'captcha.enable' ) ){
			$configCaptcha	= $this->env->getConfig()->getAll( 'module.ui_captcha.', TRUE );
			if( !$configCaptcha->get( 'active' ) )
				$this->messenger->noteFailure( 'Module "UI_Captcha" needs to be installed to use CAPTCHA.' );
			else
				$this->useCaptcha	= $configCaptcha->get( 'mode' );
		}
		if( $this->moduleConfig->get( 'csrf.enable' ) ){
			$configCsrf	= $this->env->getConfig()->getAll( 'module.security_csrf.', TRUE );
			if( !$this->env->getModules()->has( 'Security_CSRF' ) ){
				$this->messenger->noteFailure( 'Module "Security_CSRF" needs to be installed.' );
				$this->env->getLog()->log( 'warn', 'Module "Security_CSRF" needs to be installed.' );
			}
//	@todo activate these lines after module Security:CSRF got config switch "active", maybe at version 0.2.8
//			else if( !$configCsrf->get( 'active' ) ){
//				$this->messenger->noteFailure( 'Module "Security_CSRF" needs to be enabled.' );
//				$this->env->getLog()->log( 'warn', 'Module "Security_CSRF" needs to be enabled.' );
//			}
			else
				$this->useCsrf	= TRUE;
		}
		$this->useHoneypot		= $this->moduleConfig->get( 'honeypot.enable' );
	}
}
