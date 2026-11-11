<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\Alg\ID;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Manage_Form_Fill_Receive extends AjaxController
{
	protected HttpRequest $request;

	protected Logic_Form_FillManager $logicFill;

	protected Model_Form $modelForm;

	protected Model_Form_Fill $modelFill;

	/**
	 *	Receive form data from external application.
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function index(): void
	{
		error_reporting( E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED );
//		ini_set( 'display_errors', FALSE );

//		$this->checkIsAjax();
		$this->sendAllowOriginHeaders();
		try{
			$this->checkIsPost();
			$data	= $this->request->getAll();
			$formId	= $this->checkMandatoryFields( $data );

			/** @var ?Entity_Form $form */
			$form		= $this->modelForm->get( $formId );
			if( NULL === $form )
				$this->respondError( 0, 'Form not existing anymore', 404 );

			$inputs	= $data['inputs'] ?? [];
			$this->filterData( $inputs );
			$this->checkCaptcha( $form, $inputs );
			$this->checkAltcha( $form, $inputs );
			$this->checkIsNotSpam( $form, $inputs );
			$this->checkIsNotBlacklistedIp( $this->env->getRequest()->getIp() );

			$fillId	= $this->createFillFromInputs( $form, $inputs );
			/** @var Entity_Form_Fill $fill */
			$fill	= $this->modelFill->get( $fillId );
			$this->applyActionsOnCreatedFill( $form, $fill );

			$status	= 'ok';
			$data	= [
				'formId'	=> $form->formId,
				'formType'	=> $form->type,
				'fillId'	=> $fillId,
				'redirect'	=> $form->forwardOnSubmit,
			];
		}
		catch( Exception $e ){
			$payload	= ['exception' => $e];
			$this->env->getCaptain()->callHook( 'Env', 'logException', $this, $payload );
//			$this->logicFill->sendManagerErrorMail( @$data );
			$status	= 'error';
			$data	= [
				'error'		=> $e->getMessage(),
				'trace'		=> $e->getTraceAsString(),
				'formId'	=> $form?->formId ?? '',
				'formType'	=> $form?->type ?? '',
			];
		}
		header( 'Content-Type: application/json' );
		print( json_encode( ['status' => $status, 'data' => $data] ) );
		exit;
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->modelForm	= new Model_Form( $this->env );
		$this->modelFill	= new Model_Form_Fill( $this->env );
		$this->logicFill	= new Logic_Form_FillManager( $this->env );
	}

	/**
	 *	@param		Entity_Form			$form
	 *	@param		Entity_Form_Fill	$fill
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function applyActionsOnCreatedFill( Entity_Form $form, Entity_Form_Fill $fill ): void
	{
		if( Model_Form::TYPE_NORMAL === $form->type ){
			$this->logicFill->sendCustomerResultMail( $fill );
			$this->logicFill->sendManagerResultMails( $fill );
			$this->logicFill->applyTransfers( $fill );
		}
		else if( Model_Form::TYPE_CONFIRM === $form->type ){
			$this->logicFill->sendConfirmMail( $fill );
		}
	}

	/**
	 *	@param		Entity_Form		$form
	 *	@param		array			$inputs
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 */
	protected function checkAltcha( Entity_Form $form, array & $inputs ): void
	{
		if( !$form->useAltcha )
			return;
		$module	= $this->env->getModules()->get( 'Security_Altcha', TRUE, FALSE );
		if( NULL === $module )
			return;

		$altcha	= '';
		foreach( $inputs as $nr => $input ){
			if( 'altcha' === ( $input['name'] ?? '' ) ){
				$altcha	= trim( $input['value'] );
				unset( $inputs[$nr] );
			}
		}
		if( '' === $altcha )
			$this->respondError( 0, 'Form field "altcha" is missing', 406 );

		if( Logic_Altcha::getInstance( $this->env )->verify( $altcha )->verified )
			return;
		$this->respondError( 0, 'Invalid solution of ALTCHA challenge', 406 );

	}

	/**
	 *	@param		Entity_Form		$form
	 *	@param		array			$inputs
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 */
	protected function checkCaptcha( Entity_Form $form, array & $inputs ): void
	{
		$captcha	= '';
		foreach( $inputs as $nr => $input ){
			if( 'captcha' === ( $input['name'] ?? '' ) ){
				$captcha	= $input['value'];
				unset( $inputs[$nr] );
			}
		}
		if( $captcha ){
			if( !View_Helper_Captcha::checkCaptcha( $this->env, $captcha ) )
				$this->respondData( ['status' => 'captcha', 'data' => [
					'captcha'	=> $captcha,
					'real'		=> $this->env->getSession()->get( 'captcha' ),
					'formId'	=> $form->formId,
					'formType'	=> @$form->type,
				] ], 406 );
		}
	}

	/**
	 *	Checks whether the current request is done via AJAX.
	 *	Throws exception in strict mode.
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 */
	protected function checkIsAjax(): void
	{
		if( $this->request->isAjax() )
			return;
		$this->respondError( 0, 'AJAX requests allowed only', 405 );
	}

	/**
	 *	@param		?string		$ip			Client IP address
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 */
	protected function checkIsNotBlacklistedIp( ?string $ip = NULL ): void
	{
		if( !$this->env->getModules()->has( 'Security:IP:Check' ) )
			return;
		$logic	= Logic_IP_Check::getInstance( $this->env );

		if( '' === ( $ip ?? '' ) )
			$ip	= $this->env->getRequest()->getIp();

		/** @var array{ip: string, ip_version: int, clean: bool, score: int, sources: array<string, array{listed: bool, description: string}>} $result */
		$result	= $logic->checkIp( $ip );
		if( $result['clean'] )
			return;
		$this->respondData( ['status' => 'ip', 'message' => 'IP is blacklisted' ], 406 );
	}

	/**
	 *	Checks whether the current request is done via POST.
	 *	Responds HTTP status code 405 Method Not Allowed with return code 0 and empty message, if not.
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 */
	protected function checkIsPost(): void
	{
		if( !$this->request->getMethod()->isPost() )
			$this->respondError( 0, NULL, 405 );
	}

	/**
	 *	Checks whether form ID and form inputs are given.
	 *	Returns found form ID.
	 *	@param		array		$data		Form data containing list of inputs with augmented data
	 *	@return		int
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 */
	protected function checkMandatoryFields( array $data ): int
	{
		$inputs	= $data['inputs'] ?? [];
		if( [] === $inputs )
			$this->respondError( 0, 'No form data inputs given', 400 );

		$formId	= trim( $data['formId'] ?? '' );
		if( '' === $formId )
			$this->respondError( 0, 'No form ID given', 400 );

		if( !preg_match( '/^[0-9]+$/', $formId ) )
			$this->respondError( 0, 'Invalid form ID given', 400 );

		return $formId;
	}

	/**
	 *	@param		Entity_Form		$form
	 *	@param		array			$inputs
	 *	@return		void
	 *	@throws		RuntimeException			if given static class method is not existing
	 *	@throws		RuntimeException			if method call produces stdout output, for example warnings and notices
	 *	@throws		RuntimeException			if method call is throwing an exception
	 *	@throws		DomainException
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 */
	protected function checkIsNotSpam( Entity_Form $form, array $inputs ): void
	{
		$payload	= [
			'form'		=> $form,
			'inputs'	=> $inputs,
			'isSpam'	=> FALSE,
		];
		$this->env->getCaptain()->callHookWithPayload( 'Form:Fill', 'checkSpam', $this, $payload );

		if( !$payload['isSpam'] )
			return;
		$this->respondError( 0, 'SPAM detected', 406 );
	}

	/**
	 *	@param		Entity_Form		$form
	 *	@param		array			$inputs
	 *	@return		int|string
	 */
	protected function createFillFromInputs( Entity_Form $form, array $inputs ): int|string
	{
		$status	= match( $form->type ){
			Model_Form::TYPE_CONFIRM	=> Model_Form_Fill::STATUS_NEW,
			default						=> Model_Form_Fill::STATUS_CONFIRMED,
		};

		$entity	= Entity_Form_Fill::fromArray( [
			'formId'	=> $form->formId,
			'status'	=> $status,
			'email'		=> $inputs['email']['value'] ?? '',
//				'data'		=> json_encode( $data['inputs'], JSON_PRETTY_PRINT ),
			'data'		=> json_encode( $inputs ),
			'referer'	=> getEnv( 'HTTP_REFERER' ) ? strip_tags( getEnv( 'HTTP_REFERER' ) ) : '',
			'agent'		=> strip_tags( getEnv( 'HTTP_USER_AGENT' ) ),
			'createdAt'	=> time(),
		] );
		if( Model_Form_Fill::STATUS_NEW === $entity->status )
			if( property_exists( $entity, 'token' ) )
				$entity->token	= sha1( ID::uuid() );
		return $this->modelFill->add( $entity, FALSE );
	}

	protected function filterData( array & $inputs ): void
	{
		foreach( $inputs as $input )
			if( isset( $input['value'] ) )
				$input['value'] = trim( strip_tags( $input['value'] ) );
	}

	protected function sendAllowOriginHeaders(): void
	{
		$origin	= $this->env->getConfig()->get( 'module.manage_forms.origin' );
		$origin	= $origin ?: $this->env->getBaseUrl();
		$origin	= rtrim( $origin, '/' );
		header( 'Access-Control-Allow-Origin: '.$origin );
		header( 'Access-Control-Allow-Credentials: true' );
	}
}
