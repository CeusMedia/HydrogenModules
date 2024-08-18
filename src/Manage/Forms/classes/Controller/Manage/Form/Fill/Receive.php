<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

class Controller_Manage_Form_Fill_Receive extends Controller
{
	protected HttpRequest $request;

	protected Logic_Form_Fill $logicFill;

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
		$origin	= $this->env->getConfig()->get( 'module.manage_forms.origin' );
		$origin	= $origin ?: $this->env->getBaseUrl();
		$origin	= rtrim( $origin, '/' );
		header( 'Access-Control-Allow-Origin: '.$origin );
		header( 'Access-Control-Allow-Credentials: true' );
//		ini_set( 'display_errors', FALSE );
//		$this->checkIsAjax();
		try{
			$this->checkIsPost();
			$data	= $this->request->getAll();
			if( !isset( $data['inputs'] ) || !$data['inputs'] )
				throw new Exception( 'No form data given.' );
			if( !( $formId = $this->request->get( 'formId' ) ) )
				throw new Exception( 'No form ID given.' );
			if( !preg_match( '/^[0-9]+$/', $formId ) )
				throw new Exception( 'Invalid form ID given.' );
			$form		= $this->modelForm->get( $formId );
//			if( $data['inputs']['surname']['value'] === "Testmann" )
//				throw new Exception( 'Hallo Herr Testmann!' );
			$email		= '';
			$captcha	= '';
			foreach( $data['inputs'] as $nr => $input ){
				if( $input['name'] === 'email' )
					$email	= strip_tags( $input['value'] );
				if( $input['name'] === 'captcha' ){
					$captcha	= $input['value'];
					unset( $data['inputs'][$nr] );
				}
			}
			if( $captcha ){
				if( !View_Helper_Captcha::checkCaptcha( $this->env, $captcha ) ){
					header( 'Content-Type: application/json' );
					print( json_encode( ['status' => 'captcha', 'data' => [
						'captcha'	=> $captcha,
						'real'		=> $this->env->getSession()->get( 'captcha' ),
						'formId'	=> $formId,
						'formType'	=> @$form->type,
					] ] ) );
					exit;
				}
			}
			if( !isset( $input) )
				throw new DomainException( 'No form ID given.' );

			$status		= Model_Form_Fill::STATUS_CONFIRMED;
			if( $form->type == Model_Form::TYPE_CONFIRM )
				$status	= Model_Form_Fill::STATUS_NEW;

			foreach( $data['inputs'] as $input )
				$input['value']	= strip_tags( $input['value'] );

			$data		= [
				'formId'	=> $formId,
				'status'	=> $status,
				'email'		=> strip_tags( $email ),
//				'data'		=> json_encode( $data['inputs'], JSON_PRETTY_PRINT ),
				'data'		=> json_encode( $data['inputs'] ),
				'referer'	=> getEnv( 'HTTP_REFERER' ) ? strip_tags( getEnv( 'HTTP_REFERER' ) ) : '',
				'agent'		=> strip_tags( getEnv( 'HTTP_USER_AGENT' ) ),
				'createdAt'	=> time(),
			];
			$fillId		= $this->modelFill->add( $data, FALSE );
			if( Model_Form::TYPE_NORMAL === (int) $form->type ){
				$this->logicFill->sendCustomerResultMail( $fillId );
				$this->logicFill->sendManagerResultMails( $fillId );
				$this->logicFill->applyTransfers( $fillId );
			}
			else if( Model_Form::TYPE_CONFIRM === (int) $form->type ){
				$this->logicFill->sendConfirmMail( $fillId );
			}
			$status	= 'ok';
			$data	= [
				'formId'	=> $form->formId,
				'formType'	=> $form->type,
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
				'formId'	=> @$form->formId,
				'formType'	=> @$form->type,
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
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicFill	= $this->getLogic( 'formFill' );
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
}
