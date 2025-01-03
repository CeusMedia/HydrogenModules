<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Info_Contact extends Controller
{
	public static string $moduleId	= 'Info_Contact';

	protected HttpRequest $request;
	protected MessengerResource $messenger;

	protected ?string $useCaptcha	= NULL;
	protected bool $useCsrf;
	protected bool $useHoneypot;
	protected bool $useNewsletter;

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function index(): void
	{
		if( $this->request->getMethod()->isPost() && $this->request->has( 'save' ) ){
			$this->saveInput();
			$this->restart( NULL, TRUE );
		}

		$this->collectNewsletterTopicsIfEnabled();

		if( 'default' === $this->useCaptcha ){
			$this->addData( 'captchaLength', $this->moduleConfig->get( 'captcha.length' ) );
			$this->addData( 'captchaStrength', $this->moduleConfig->get( 'captcha.strength' ) );
		}
		$this->addData( 'formPath', $this->getFormPath() );
		$this->addData( 'fullname', $this->request->get( 'fullname', '' ) );
		$this->addData( 'email', $this->request->get( 'email', '' ) );
		$this->addData( 'subject', $this->request->get( 'subject', '' ) );
		$this->addData( 'message', $this->request->get( 'message', '' ) );
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->messenger		= $this->env->getMessenger();
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
		$this->useNewsletter	= (bool) $this->moduleConfig->get( 'newsletter.enable' );
		$this->useHoneypot		= (bool) $this->moduleConfig->get( 'honeypot.enable' );

		$this->addData( 'useCaptcha', $this->useCaptcha );
		$this->addData( 'useCsrf', $this->useCsrf );
		$this->addData( 'useNewsletter', $this->useNewsletter );
		$this->addData( 'useHoneypot', $this->useHoneypot );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\CeusMedia\HydrogenFramework\Environment\Exception
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function applyNewsletterForwardingIfEnabled(): void
	{
		if( $this->useNewsletter && $this->request->has( 'newsletter' ) ){
			if( $this->env->getModules()->has( 'Resource_Newsletter' ) ){
				$path	= 'info/newsletter';
				if( $this->env->getModules()->has( 'Info_Pages' ) ){
					$logicPage	= Logic_Page::getInstance( $this->env );
					/** @var ?Entity_Page $page */
					$page	= $logicPage->getPageFromControllerAction( 'Info_Newsletter', 'index', FALSE );
					if( NULL === $page )
						/** @var ?Entity_Page $page */
						$page	= $logicPage->getPageFromController( 'Info_Newsletter', FALSE );
					if( NULL !== $page )
						$path	= $page->fullpath;
				}
				$fullname	= trim( $this->request->get( 'fullname' ) );
				$parts		= preg_split( '/\s+/', $fullname.' ', 2 );
				$path		= $path.'?'.http_build_query( [
						'fullname'		=> $fullname,
						'firstname'		=> trim( $parts[0] ),
						'surname'		=> trim( $parts[1] ),
						'email'			=> $this->request->get( 'email' ),
						'groups'		=> $this->request->get( 'topics' ),
					], '', '&' );
				$this->restart( $path );
			}
		}
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function collectNewsletterTopicsIfEnabled(): void
	{
		if( !$this->useNewsletter )
			return;
		$topics		= [];
		if( $this->env->getModules()->has( 'Resource_Newsletter' ) ){
			$model		= new Model_Newsletter_Group( $this->env );
			$conditions	= [
				'status'	=> Model_Newsletter_Group::STATUS_USABLE,
				'type'		=> [
					Model_Newsletter_Group::TYPE_DEFAULT,
					Model_Newsletter_Group::TYPE_AUTOMATIC
				] ];
			$orders		= ['title' => 'ASC'];
			$topics		= $model->getAll( $conditions, $orders );
		}
		$this->addData( 'newsletterTopics', $topics );
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function getFormPath(): string
	{
		$path	= "./info/contact";
		if( $this->env->getModules()->has( 'Info_Pages' ) ){
			$model	= new Model_Page( $this->env );
			$page	= $model->getByIndex( 'controller', 'Info_Contact' );
			if( !empty( $page->fullpath ) )
				$path	= "./".$page->fullpath;
			else{
				$path	= "./".$page->identifier;
				if( $page->parentId ){
					$parent = $model->get( $page->parentId );
					$path	= "./".$parent->identifier.'/'.$page->identifier;
				}
			}
		}
		return $path;
	}

	/**
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function saveInput(): bool
	{
		try{
			/** @var Dictionary $inputData */
			$inputData		= $this->request->getAll( '', TRUE );
			$logic			= new Logic_Info_Contact( $this->env );
			$words			= $this->getWords( 'index' );
			$errorsOrTrue	= $logic->validateInput( $inputData );
			if( TRUE !== $errorsOrTrue ){
				foreach( $errorsOrTrue as $error )
					$this->messenger->noteError( $words['msgError'.$error] );
				return FALSE;
			}
			$logic->sendDefaultMail( $inputData );
			$this->messenger->noteSuccess( $words['msgSuccess'] );

			$this->applyNewsletterForwardingIfEnabled();

			//	@todo handle newsletter registration

			return TRUE;
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $e->getMessage() );
			$this->env->getLog()->logException( $e );
		}
		return FALSE;
	}
}
