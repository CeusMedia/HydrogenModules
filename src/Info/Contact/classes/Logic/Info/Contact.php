<?php
declare(strict_types=1);

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Info_Contact extends Logic
{
	protected Dictionary $moduleConfig;

	protected ?string $useCaptcha	= NULL;
	protected bool $useCsrf			= FALSE;

	/**
	 *	Sends validated form input data via mail,
	 *  using view helper Mail_Info_Contact with template mail/info/contact.
	 *
	 *	@param		Dictionary		$data		Form input data, must be validated before
	 *	@return		bool
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function sendDefaultMail( Dictionary $data ): bool
	{
		$logic		= Logic_Mail::getInstance( $this->env );
		$mail		= new Mail_Info_Contact( $this->env, $data->getAll() );
		$receiver	= (object) ['email' => $this->moduleConfig->get( 'mail.receiver' )];
		return $logic->handleMail( $mail, $receiver, 'de' );
	}

	/**
	 *	Sends validated form input data via mail,
	 *  using view helper Mail_Info_Contact_Form with template mail/info/contact/form.
	 *
	 *	@param		Dictionary		$data		Form input data, must be validated before
	 *	@return		bool
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function sendFormMail( Dictionary $data ): bool
	{
		$logic		= Logic_Mail::getInstance( $this->env );
		$mail		= new Mail_Info_Contact_Form( $this->env, $data->getAll() );
		$receiver	= (object) ['email' => $this->moduleConfig->get( 'mail.receiver' )];
		return $logic->handleMail( $mail, $receiver, 'de' );
	}

	/**
	 *	@param		Dictionary		$data
	 *	@return		bool|array
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function validateInput( Dictionary $data ): bool|array
	{
		$errors		= [];
		if( '' === trim( $data->get( 'fullname', '' ) ) )
			$errors[]	= 'FullNameMissing';
		if( '' === trim( $data->get( 'email', '' ) ) )
			$errors[]	= 'EmailMissing';
		if( '' === trim( $data->get( 'subject', '' ) ) )
			$errors[]	= 'SubjectMissing';
		if( '' === trim( $data->get( 'message', '' ) ) )
			$errors[]	= 'MessageMissing';
		if( '' !== $data->get( 'trap', '' ) )
			$errors[]	= 'AccessDenied';

		if( $this->useCsrf ){
			$logicCsrf	= Logic_CSRF::getInstance( $this->env );
			if( !$logicCsrf->verifyToken(
				$data->get( 'csrf_form_name', '' ),
				$data->get( 'csrf_token', '' )
			) )
				$errors[]	= 'CsrfFailed';
		}

		$quotedUrl	= preg_quote( $this->env->url, '/' );
		if( !preg_match( '/^'.$quotedUrl.'/', getEnv( 'HTTP_REFERER' ) ) )
			$errors[]	= 'RefererInvalid';

		if( $this->useCaptcha ){
			$captchaWord	= $data->get( 'captcha' );
			if( '' !== $captchaWord || !$this->env->getRequest()->isAjax() )
				if( !View_Helper_Captcha::checkCaptcha( $this->env, $captchaWord ) )
					$errors[]	= 'CaptchaFailed';
		}

		return [] === $errors ? TRUE : $errors;
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->moduleConfig		= $this->env->getConfig()->getAll( "module.info_contact.", TRUE );

		$this->useCaptcha		= NULL;
		$this->useCsrf			= FALSE;

		if( $this->moduleConfig->get( 'captcha.enable' ) ){
			$configCaptcha	= $this->env->getConfig()->getAll( 'module.ui_captcha.', TRUE );
			if( !$configCaptcha->get( 'active' ) ) {
				$this->env->getLog()->log( 'warn', 'Module "UI_Captcha" needs to be installed.' );
				throw new RuntimeException( 'Module "UI_Captcha" needs to be installed to use CAPTCHA.' );
			}
			$this->useCaptcha	= $configCaptcha->get( 'mode' );
		}
		if( $this->moduleConfig->get( 'csrf.enable' ) ){
			$configCsrf	= $this->env->getConfig()->getAll( 'module.security_csrf.', TRUE );
			if( !$this->env->getModules()->has( 'Security_CSRF' ) ){
				$this->env->getLog()->log( 'warn', 'Module "Security_CSRF" needs to be installed.' );
				throw new RuntimeException( 'Module "Security_CSRF" needs to be installed.' );
			}
			else if( !$configCsrf->get( 'active' ) ){
				$this->env->getLog()->log( 'warn', 'Module "Security_CSRF" needs to be enabled.' );
				throw new RuntimeException( 'Module "Security_CSRF" needs to be enabled.' );
			}
			if( !$this->env->getRequest()->isAjax() )
				$this->useCsrf	= TRUE;
		}
	}
}