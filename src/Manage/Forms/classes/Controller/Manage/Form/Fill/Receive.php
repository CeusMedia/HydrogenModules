<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

class Controller_Manage_Form_Fill_Receive extends Controller
{
	protected HttpRequest $request;

	protected Logic_Form_FillManager $logicFill;

	protected Model_Form $modelForm;

	protected Model_Form_Fill $modelFill;

	/**
	 *	Constructor, disables automatic view instance.
	 *	@param		WebEnvironment		$env
	 *	@throws		ReflectionException
	 */
	public function __construct( WebEnvironment $env )
	{
		parent::__construct( $env, FALSE );
	}

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
				throw new DomainException( 'Form not existing anymore.' );

			$inputs	= $data['inputs'] ?? [];
			$this->filterData( $inputs );
			$this->checkCaptcha( $form, $inputs );
			$fillId	= $this->createFillFromInputs( $form, $inputs );
			$this->applyActionsOnCreatedFill( $form, $fillId );

			$status	= 'ok';
			$data	= [
				'formId'	=> $form->formId,
				'formType'	=> $form->type,
				'fillId'	=> $fillId,
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
	 *	@param		Entity_Form		$form
	 *	@param		int|string		$fillId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function applyActionsOnCreatedFill( Entity_Form $form, int|string $fillId ): void
	{
		if( Model_Form::TYPE_NORMAL === $form->type ){
			$this->logicFill->sendCustomerResultMail( $fillId );
			$this->logicFill->sendManagerResultMails( $fillId );
			$this->logicFill->applyTransfers( $fillId );
		}
		else if( Model_Form::TYPE_CONFIRM === $form->type ){
			$this->logicFill->sendConfirmMail( $fillId );
		}
	}

	/**
	 *	@param		Entity_Form		$form
	 *	@param		array			$inputs
	 *	@return		void
	 */
	protected function checkCaptcha( Entity_Form $form, array & $inputs ): void
	{
		$captcha	= '';
		foreach( $inputs as $nr => $input ){
			if( $input['name'] === 'captcha' ){
				$captcha	= $input['value'];
				unset( $inputs[$nr] );
			}
		}
		if( $captcha ){
			if( !View_Helper_Captcha::checkCaptcha( $this->env, $captcha ) ){
				header( 'Content-Type: application/json' );
				print( json_encode( ['status' => 'captcha', 'data' => [
					'captcha'	=> $captcha,
					'real'		=> $this->env->getSession()->get( 'captcha' ),
					'formId'	=> $form->formId,
					'formType'	=> @$form->type,
				] ] ) );
				exit;
			}
		}
	}

	/**
	 *	Checks whether the current request is done via AJAX.
	 *	Throws exception in strict mode.
	 *	@param		bool		$strict		Flag: throw exception if not AJAX and strict mode (default)
	 *	@return		bool
	 */
	protected function checkIsAjax( bool $strict = TRUE ): bool
	{
		if( $this->request->isAjax() )
			return TRUE;
		if( $strict )
			throw new RuntimeException( 'AJAX requests allowed only' );
		return FALSE;
	}

	/**
	 *	Checks whether the current request is done via POST.
	 *	Throws exception in strict mode.
	 *	@param		bool		$strict		Flag: throw exception if not POST and strict mode (default)
	 *	@return		bool
	 *	@throws		RuntimeException		if request method is not POST and strict mode is enabled
	 */
	protected function checkIsPost( bool $strict = TRUE ): bool
	{
		if( $this->request->getMethod()->isPost() )
			return TRUE;
		if( $strict )
			throw new RuntimeException( 'Access denied: POST requests, only' );
		return FALSE;
	}

	protected function checkMandatoryFields( array $data ): int
	{
		$inputs	= $data['inputs'] ?? [];
		if( [] === $inputs )
			throw new Exception( 'No form data inputs given.' );

		$formId	= trim( $data['formId'] ?? '' );
		if( '' === $formId )
			throw new Exception( 'No form ID given.' );

		if( !preg_match( '/^[0-9]+$/', $formId ) )
			throw new Exception( 'Invalid form ID given.' );

		return $formId;
	}

	/**
	 *	@param		Entity_Form		$form
	 *	@param		array			$inputs
	 *	@return		int|string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function createFillFromInputs( Entity_Form $form, array $inputs ): int|string
	{
		$status	= match( $form->type ){
			Model_Form::TYPE_CONFIRM	=> Model_Form_Fill::STATUS_NEW,
			default						=> Model_Form_Fill::STATUS_CONFIRMED,
		};

		return $this->modelFill->add( Entity_Form_Fill::fromArray( [
			'formId'	=> $form->formId,
			'status'	=> $status,
			'email'		=> $inputs['email']['value'] ?? '',
//				'data'		=> json_encode( $data['inputs'], JSON_PRETTY_PRINT ),
			'data'		=> json_encode( $inputs ),
			'referer'	=> getEnv( 'HTTP_REFERER' ) ? strip_tags( getEnv( 'HTTP_REFERER' ) ) : '',
			'agent'		=> strip_tags( getEnv( 'HTTP_USER_AGENT' ) ),
			'createdAt'	=> time(),
		] ), FALSE );
	}

	protected function filterData( array & $inputs ): void
	{
		foreach( $inputs as $input )
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
