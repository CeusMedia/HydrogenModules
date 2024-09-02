<?php
/**
 *	Controller for OAuth server.
 *
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\Net\HTTP\Status as HttpStatus;
use CeusMedia\HydrogenFramework\Controller;

/**
 *	Controller for OAuth server.
 *
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 *	@todo			code doc
 *	@todo			todos within code
 *	@todo			add response headers: "Cache-Control: no-store", "Pragma: no-cache"
 */
class Controller_Oauth extends Controller
{
	/**	@var	HttpRequest						$request */
	protected HttpRequest $request;

	/**	@var	integer							$lifetimeAccessToken		Seconds until access token expires */
	protected int $lifetimeAccessToken			= 3600;

	/**	@var	integer							$lifetimeAuthorizationCode	Seconds until authorization code expires */
	protected int $lifetimeAuthorizationCode	= 600;

	/**	@var	integer							$lifetimeRefreshToken		Seconds until refresh token expires */
	protected int $lifetimeRefreshToken			= 1209600;

	protected bool $flagSendRefreshTokenOnAuthorizationCodeGrant	= TRUE;

	protected bool $flagSendRefreshTokenOnPasswordGrant				= TRUE;

	protected bool $flagRefreshRefreshToken							= FALSE;

	/**
	 *	... unfinished, undocumented ...
	 *	Supported grant types: authorization code (missing: implicit)
	 *	@access		public
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@see		http://tools.ietf.org/html/rfc6749#section-4.1.2.1
	 *	@todo		#1 implement error types: unauthorized_client, invalid_scope, server_error, temporarily_unavailable
	 *	@todo		#2 implement grant type: implicit
	 *	@todo		#3 make configurable: client agent (default, RFC) OR show login fail on authorization server (nicer)
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function authorize(): void
	{
#		if( $this->request->getMethod() !== "GET" )
#			$this->errorRedirect( 'GET request required.', 'This request must use the GET method.' );
		if( !strlen( trim( $redirectUri = $this->request->get( 'redirect_uri' ) ) ) )
			$this->errorReport( 'Missing redirect URI.' );
		if( !strlen( trim( $clientId = $this->request->get( 'client_id' ) ) ) )
			$this->errorReport( 'Missing client ID.' );
		if( !strlen( trim( $responseType = $this->request->get( 'response_type' ) ) ) )
			$this->errorRedirect( 'invalid_request', 'Missing response type.' );
		if( !strlen( trim( $state = $this->request->get( 'state' ) ) ) )
			$this->errorRedirect( 'invalid_request', 'Missing state.' );
		switch( $responseType ){
			case 'code':							//  grant type: authorization code
				/*  --  VALIDATION OF CLIENT ID  --  */
				if( !( $applicationId = $this->getApplicationIdFromClientId( $clientId ) ) )		//  no application found for client ID
					$this->errorRedirect( 'invalid_client' );
				if( $this->request->getMethod()->isPost() ){
					$logic		= Logic_Authentication::getInstance( $this->env );
					$modelUser	= new Model_User( $this->env );
					if( !( $user = $modelUser->getByIndex( 'username', $this->request->get( 'username' ) ) ) )
						$this->errorRedirect( 'access_denied' );
					if( !$logic->checkPassword( $user->userId, $this->request->get( 'password' ) ) ){
						$this->errorRedirect( 'access_denied' );

/*						//  TODO #3: THIS WOULD BE NICE BUT IS NOT RFC-STYLE
						//  - to not redirect to client agent with error message
						//  - but show error message on login page at authorization server
						$url	= './oauth/authorize?'.http_build_query( [
							'client_id'		=> $clientId,
							'response_type'	=> $responseType,
							'redirect_uri'	=> $redirectUri,
							'state'			=> $state,
						] );
						$this->errorReport( 'Login failed.', $uri );*/
					}
					$scope	= trim( $this->request->get( 'scope' ) );
					$code	= $this->generateAuthorizationCode( $applicationId, (string) $user->userId, $redirectUri, $scope );
					$url	= $redirectUri.'?'.http_build_query( [
						'code'	=> $code,
						'state'	=> $state,
					] );
					$this->restart( $url, FALSE, 302, TRUE );
				}
				$model			= new Model_Oauth_Application( $this->env );
				$application	= $model->getByIndex( 'clientId', $clientId );
				$this->addData( 'application', $application );
				$this->addData( 'clientId', $clientId );
				$this->addData( 'responseType', $responseType );
				$this->addData( 'redirectUri', $redirectUri );
				$this->addData( 'scope', $this->request->get( 'scope' ) );
				$this->addData( 'state', $state );
				break;
			case 'token':							//  grant type: implicit
				break;
			default:
				$this->errorRedirect( 'unsupported_response_type', 'Invalid response type: '.$responseType );
		}
	}

	public function index()
	{
	}

	/**
	 *	...
	 *	Supported grant types: authorization_code, password, client_credentials, refresh_token
	 *	@access		public
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function token(): void
	{
		if( !strlen( trim( $grantType = $this->request->get( 'grant_type' ) ) ) )
			$this->errorResponse( 'invalid_request', 'Missing grant type.' );

		switch( $grantType ){
			case 'authorization_code':				//  grant type: authorization code
				$this->tokenAuthorizationCode();
				break;
			case 'password':						//  grant type: resource owner password credentials
				$this->tokenPassword();
				break;
			case 'client_credentials':				//  grant type: client credentials
				$this->tokenClient();
				break;
			case 'refresh_token':
				$this->tokenRefreshToken();
				break;
			default:
				$this->errorResponse( 'invalid_grant', 'Invalid grant type: '.$grantType );
		}
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function __onInit(): void
	{
		$this->request	= $this->env->getRequest();
		$config			= $this->env->getConfig()->getAll( 'module.server_oauth.', TRUE );
		$this->lifetimeAccessToken			= $config->get( 'lifetime.access' );
		$this->lifetimeAuthorizationCode	= $config->get( 'lifetime.code' );
		$this->lifetimeRefreshToken			= $config->get( 'lifetime.refresh' );
		$this->cleanUp();
	}


	/**
	 *	Removes all expired access tokens, authorization codes and refresh tokens.
	 *	@access		protected
	 *	@return		void
	 *	@todo		idea: return list of refreshed tokens/codes
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function cleanUp(): void
	{
		$modelCode			= new Model_Oauth_Code( $this->env );
		$modelApplication	= new Model_Oauth_Application( $this->env );
		$modelAccessToken	= new Model_Oauth_AccessToken( $this->env );
		$modelRefreshToken	= new Model_Oauth_RefreshToken( $this->env );

		$expired		= time() - $this->lifetimeAuthorizationCode;
		foreach( $modelCode->getAll( ['createdAt' => '< '.$expired] ) as $entry )
			$modelCode->remove( $entry->oauthCodeId );

		$expired		= time() - $this->lifetimeAccessToken;
		foreach( $modelAccessToken->getAll( ['createdAt' => '< '.$expired] ) as $entry )
			$modelAccessToken->remove( $entry->oauthAccessTokenId );

		$expired		= time() - $this->lifetimeRefreshToken;
		foreach( $modelRefreshToken->getAll( ['createdAt' => '< '.$expired] ) as $entry )
			$modelRefreshToken->remove( $entry->oauthRefreshTokenId );
	}

	/**
	 *	Tries to take client ID and secret from basic authentication header.
	 *	@access		protected
	 *	@return		object|NULL		Data object containing client ID and secret if detected, NULL otherwise
	 */
	protected function decodeBasicAuthentication(): ?object
	{
		$headers    = getallheaders();
		if( !empty( $headers['Authorization'] ) ){
			if( str_starts_with( $headers['Authorization'], 'Basic ' ) ){
				$code	= preg_replace( "/^Basic /", "", $headers['Authorization'] );
				[$clientId, $clientSecret] = explode( ":", base64_decode( $code ) );
				return (object) ['clientId' => $clientId, 'clientSecret' => $clientSecret];
			}
		}
		return NULL;
	}

	protected function errorRedirect( $message, $description = NULL, $uri = NULL, $status = 302 ): void
	{
		$parameters		= ['error' => $message];
		if( strlen( trim( $description ) ) )
			$parameters['error_description']	= utf8_decode( $description );
		if( strlen( trim( $uri ) ) )
			$parameters['error_uri']	= $uri;
		if( strlen( trim( $this->request->get( 'state' ) ) ) )
			$parameters['state']	= $this->request->get( 'state' );
		$url	= $this->request->get( 'redirect_uri' ).'?'.http_build_query( $parameters );
		$this->restart( $url, FALSE, $status, TRUE );
	}

	/**
	 *	Responds to error by reporting message via messenger of local application.
	 *	Attention: URIs to redirect afterwards can only be within local application.
	 *	@access		protected
	 *	@param		string		$message		Error message to report
	 *	@param		?string		$uri			URI within local application to redirect to
	 *	@return		void
	 */
	protected function errorReport( string $message, ?string $uri = NULL ): void
	{
		$this->env->getMessenger()->noteError( $message );
		$this->restart( $uri, !$uri );
	}

	/**
	 *	Response error to client agent.
	 *	Sends content type header fo MIME type application/json.
	 *	@access		protected
	 *	@param		string		$message		Key of error message
	 *	@param		?string		$description	Description of error message (will be decoded to ASCII)
	 *	@param		?string		$uri			URI for further information
	 *	@param		int			$status			Default: 400
	 *	@return		void
	 */
	protected function errorResponse( string $message, ?string $description = NULL, ?string $uri = NULL, int $status = 400 ): void
	{
		$parameters	= ['error' => $message];
		if( strlen( trim( $description ) ) )
			$parameters['error_description']	= utf8_decode( $description );
		if( strlen( trim( $uri ) ) )
			$parameters['error_uri']	= $uri;
		$text	= HttpStatus::getText( $status );
		header( "HTTP/1.0 ".$status." ".$text );
		header( "Status: ".$status." ".$text );
		header( "Content-Type: application/json;charset=UTF-8" );
		header( "Cache-Control: no-store" );
		header( "Pragma: no-cache" );
		print( json_encode( $parameters ) );
		exit;
	}

	/**
	 *	Returns newly generated authorization code for application ID.
	 *	Needs a redirect URI. Redirection is done later by authorize method.
	 *	Stores code into database. This code will expire after first use.
	 *  This code will expire and be removed by method cleanUp automatically.
	 *	Requested scopes will be stored and validated/filtered later.
	 *	@access		public
	 *	@param		int|string	$applicationId		ID of registered application
	 *	@param		int|string	$userId				ID of authenticating user
	 *	@param		string		$redirectUri		URI to redirect to afterwards ()
	 *	@param		?string		$scope				List of scopes asked to access to
	 *	@return		string		Authorization code to be delivered to redirect URI
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generateAuthorizationCode( int|string $applicationId, int|string $userId, string $redirectUri, ?string $scope = NULL ): string
	{
		$modelCode	= new Model_Oauth_Code( $this->env );
		$code		= $modelCode->getNewCode( $applicationId, $scope );
		$codeId		= $modelCode->add( [
			'oauthApplicationId'	=> $applicationId,
			'userId'				=> $userId,
			'redirectUri'			=> $redirectUri,
			'code'					=> $code,
			'scope'					=> $scope,
			'createdAt'				=> time()
		] );
		return $code;
	}

	/**
	 *	Returns newly generated access token for application ID.
	 *	Stores token into database.
	 *  This token will expire and be removed by method cleanUp automatically.
	 *	Attention: Scopes MUST be validated/filtered by now.
	 *	@access		protected
	 *	@param		int|string	$applicationId		ID of registered application
	 *	@param		int|string	$userId				ID of authenticated user
	 *	@param		?string		$scope				List of scopes to grant access to
	 *	@param		?string		$salt				Token hash salt (optional)
	 *	@param		?string		$pepper				Token hash pepper (optional)
	 *	@return		string		Access token
	 *	@todo		implement scope validation/filtering beforehand
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generateAccessToken( int|string $applicationId, int|string $userId, ?string $scope = NULL, ?string $salt = NULL, ?string $pepper = NULL ): string
	{
		$modelToken	= new Model_Oauth_AccessToken( $this->env );
		$token		= $modelToken->getNewToken( $applicationId, $scope, $salt, $pepper );
		$tokenId	= $modelToken->add( [
			'oauthApplicationId'	=> $applicationId,
			'userId'				=> $userId,
			'token'					=> $token,
			'scope'					=> (string) $scope,
			'createdAt'				=> time(),
		] );
		return $token;
	}

	/**
	 *	Returns newly generated refresh token for application ID.
	 *	Stores token into database.
	 *  This token will expire and be removed by method cleanUp automatically.
	 *	Attention: Scopes MUST be validated/filtered by now.
	 *	@access		protected
	 *	@param		int|string	$applicationId		ID of registered application
	 *	@param		?string		$scope				List of scopes to grant access to
	 *	@param		?string		$salt				Token hash salt (optional)
	 *	@param		?string		$pepper				Token hash pepper (optional)
	 *	@return		string		Access token
	 *	@todo		implement scope validation/filtering beforehand
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generateRefreshToken( int|string $applicationId, ?string $scope = NULL, ?string $salt = NULL, ?string $pepper = NULL ): string
	{
		$modelRefresh	= new Model_Oauth_RefreshToken( $this->env );
		$token			= $modelRefresh->getNewToken( $applicationId, $scope, $salt, $pepper );
		$refreshId		= $modelRefresh->add( [
			'oauthApplicationId'	=> $applicationId,
			'token'					=> $token,
			'scope'					=> (string) $scope,
			'createdAt'				=> time(),
		] );
		return $token;
	}

	/**
	 *	... unfinished helper method ...
	 *	@access		protected
	 *	@param		string			$clientId		Client ID
	 *	@return		integer|NULL	Application ID or NULL if not available
	 *	@todo		refactor to getApplicationFromClientId, delivering application data object instead of ID
	 */
	protected function getApplicationIdFromClientId( string $clientId ): ?int
	{
		$model			= new Model_Oauth_Application( $this->env );
		$application	= $model->getByIndex( 'clientId', $clientId );
		if( $application )
			return $application->oauthApplicationId;
		return NULL;
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function tokenAuthorizationCode(): void
	{
		$modelCode			= new Model_Oauth_Code( $this->env );									//  connect storage of authorization codes
		$modelApplication	= new Model_Oauth_Application( $this->env );							//  connect storage of applications
		if( !strlen( trim( $code = $this->request->get( 'code' ) ) ) )								//  if authorization code is not in request
			$this->errorResponse( 'invalid_request', 'Missing authorization code.' );				//  respond error
		if( !strlen( trim( $redirectUri = $this->request->get( 'redirect_uri' ) ) ) )				//  if redirect URI is not in request
			$this->errorResponse( 'invalid_request', 'Missing redirect URI.' );						//  respond error

		if( ( $client = $this->decodeBasicAuthentication() ) ){										//  try to find basic authentication
			$clientId		= $client->clientId;													//  get client ID from basic authentication
			$clientSecret	= $client->clientSecret;												//  get client secret from basic authentication
		}
		else{																						//  try to find client authorization in POST data
			if( !strlen( trim( $clientId = $this->request->get( 'client_id' ) ) ) )					//  if client ID is not in request
				$this->errorResponse( 'invalid_client', 'Missing client ID.', NULL, 401 );			//  respond error
			if( !strlen( trim( $clientSecret = $this->request->get( 'client_secret' ) ) ) )
				$this->errorResponse( 'invalid_client', 'Missing client secret.', NULL, 401 );
		}

		/*  --  VALIDATION OF CLIENT ID  --  */
		if( !( $applicationId = $this->getApplicationIdFromClientId( $clientId ) ) )				//  no application found for client ID
			$this->errorResponse( 'invalid_client', 'Invalid client ID.', NULL, 401 );				//  respond error

		/*  --  VALIDATION OF CLIENT SECRET  --  */
		$indices	= ['clientId' => $clientId, 'clientSecret' => $clientSecret];			//  indices to find authorized application
		if( !( $application = $modelApplication->getByIndices( $indices ) ) )						//  no application found by client ID and secret
			$this->errorResponse( 'invalid_client', 'Invalid client secret.', NULL, 401 );			//  respond error

		/*  --  VALIDATION OF REDIRECT URI  --  */
		$indices	= ['oauthApplicationId' => $applicationId, 'code' => $code];
		if( !( $authCode = $modelCode->getByIndices( $indices ) ) )
			$this->errorResponse( 'invalid_request', 'Authorization code invalid or outdated.' );
		if( $authCode->redirectUri !== $redirectUri )
			$this->errorResponse( 'invalid_request', 'Redirect URI is not matching redirect URI of authorization.' );

		$token	= $this->generateAccessToken( $application->oauthApplicationId, $authCode->userId, $authCode->scope );	//  generate, store and get access token
		$data	= [
			'access_token'	=> $token,
			'token_type'	=> 'bearer',
			'expires_in'	=> $this->lifetimeAccessToken,
		];
		if( $this->flagSendRefreshTokenOnAuthorizationCodeGrant ){
			$refreshToken	= $this->generateRefreshToken( $application->oauthApplicationId );
			$data['refresh_token']	= $refreshToken;
		}
		$data['scope']		= $authCode->scope;														//  @todo implement scope filter
		$data['user_id']	= $authCode->userId;

		$modelCode->remove( $authCode->oauthCodeId );												//  remove authorization code after single use
		$this->respondJsonData( $data );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function tokenClient(): void
	{
		if( !( $client = $this->decodeBasicAuthentication() ) )										//  no basic authentication found
			$this->errorResponse( 'invalid_client', 'Missing client authentication header', NULL, 401 );
		$clientId		= $client->clientId;													//  get client ID from basic authentication
		$clientSecret	= $client->clientSecret;												//  get client secret from basic authentication

		/*  --  VALIDATION OF CLIENT ID  --  */
		if( !( $applicationId = $this->getApplicationIdFromClientId( $clientId ) ) )				//  no application found for client ID
			$this->errorResponse( 'invalid_client', 'Invalid client ID.', NULL, 401 );				//  respond error

		/*  --  VALIDATION OF CLIENT SECRET  --  */
		$modelApplication	= new Model_Oauth_Application( $this->env );							//  connect storage of applications
		$indices	= ['clientId' => $clientId, 'clientSecret' => $clientSecret];					//  indices to find authorized application
		if( !( $application = $modelApplication->getByIndices( $indices ) ) )						//  no application found by client ID and secret
			$this->errorResponse( 'invalid_client', 'Invalid client secret.', NULL, 401 );			//  respond error

		$data	= [
			'access_token'	=> $this->generateAccessToken( $applicationId, 0 ),
			'token_type'	=> 'bearer',
			'expires_in'	=> $this->lifetimeRefreshToken,
		];
		$this->respondJsonData( $data );
	}

	/**
	 *	@todo		protect against brute force attacks (http://tools.ietf.org/html/rfc6749#section-4.3.2)
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function tokenPassword(): void
	{
		$modelApplication	= new Model_Oauth_Application( $this->env );							//  connect storage of applications
		if( !strlen( trim( $username = $this->request->get( 'username' ) ) ) )						//  if username is not in request
			$this->errorResponse( 'invalid_request', 'Missing username.' );							//  respond error
		if( !strlen( trim( $password = $this->request->get( 'password' ) ) ) )						//  if password is not in request
			$this->errorResponse( 'invalid_request', 'Missing password.' );							//  respond error

		if( ( $client = $this->decodeBasicAuthentication() ) ){										//  try to find basic authentication
			$clientId		= $client->clientId;													//  get client ID from basic authentication
			$clientSecret	= $client->clientSecret;												//  get client secret from basic authentication
		}
		else{																						//  try to find client authorization in POST data
			if( !strlen( trim( $clientId = $this->request->get( 'client_id' ) ) ) )					//  if client ID is not in request
				$this->errorResponse( 'invalid_client', 'Missing client ID.', NULL, 401 );			//  respond error
			if( !strlen( trim( $clientSecret = $this->request->get( 'client_secret' ) ) ) )
				$this->errorResponse( 'invalid_client', 'Missing client secret.', NULL, 401 );
		}

		/*  --  VALIDATION OF CLIENT ID  --  */
		if( !( $applicationId = $this->getApplicationIdFromClientId( $clientId ) ) )				//  no application found for client ID
			$this->errorResponse( 'invalid_client', 'Invalid client ID.', NULL, 401 );				//  respond error

		/*  --  VALIDATION OF CLIENT SECRET  --  */
		$indices	= ['clientId' => $clientId, 'clientSecret' => $clientSecret];			//  indices to find authorized application
		if( !( $application = $modelApplication->getByIndices( $indices ) ) )						//  no application found by client ID and secret
			$this->errorResponse( 'invalid_client', 'Invalid client secret.', NULL, 401 );			//  respond error

		if( (int) $application->type !== Model_Oauth_Application::TYPE_CONFIDENTIAL )
			$this->errorResponse( 'unauthorized_client', 'Invalid grant type for this client.' );

		$modelUser	= new Model_User( $this->env );
		if( !( $user = $modelUser->getByIndex( 'username', $username ) ) )
			$this->errorResponse( 'invalid_client', 'Invalid user', NULL, 401 );

		$logic	= Logic_UserPassword::getInstance( $this->env );
		if( !$logic->validateUserPassword( $user->userId, $password ) )
			$this->errorResponse( 'invalid_client', 'Invalid password', NULL, 401 );

		$scope	= $this->request->get( 'scope' );
		$token	= $this->generateAccessToken( $applicationId, $scope );
		$data	= [
			'access_token'	=> $token,
			'token_type'	=> 'bearer',
			'expires_in'	=> $this->lifetimeAccessToken,
		];
		if( $this->flagSendRefreshTokenOnPasswordGrant )
			$data['refresh_token']	= $this->generateRefreshToken( $applicationId, $scope );
		$data['scope']		= $scope;																//  @todo implement scope filter
		$data['user_id']	= $user->userId;
		$this->respondJsonData( $data );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function tokenRefreshToken(): void
	{
		$modelApplication	= new Model_Oauth_Application( $this->env );							//  connect storage of applications
		$modelRefresh		= new Model_Oauth_RefreshToken( $this->env );
		$refreshToken		= $this->request->get( 'refresh_token' );
		if( 0 === strlen( trim( $refreshToken ) ) )														//  if authorization code is not in request
			$this->errorResponse( 'invalid_request', 'Missing refresh token.' );		//  respond error

		$client = $this->decodeBasicAuthentication();									//  try to find basic authentication
		if( NULL === $client )
			$this->errorResponse( 'invalid_client', 'Missing basic authentication.', NULL, 401 );

		$clientId		= $client->clientId;													//  get client ID from basic authentication
		$clientSecret	= $client->clientSecret;												//  get client secret from basic authentication

		/*  --  VALIDATION OF CLIENT ID  --  */
		$applicationId	= $this->getApplicationIdFromClientId( $clientId );
		if( NULL === $applicationId )				//  no application found for client ID
			$this->errorResponse( 'invalid_client', 'Invalid client ID.', NULL, 401 );				//  respond error

		/*  --  VALIDATION OF CLIENT SECRET  --  */
		$indices	= ['clientId' => $clientId, 'clientSecret' => $clientSecret];			//  indices to find authorized application
		if( !( $application = $modelApplication->getByIndices( $indices ) ) )						//  no application found by client ID and secret
			$this->errorResponse( 'invalid_client', 'Invalid client secret.', NULL, 401 );			//  respond error

		$indices	= [																		//  indices to find refresh token
			'oauthApplicationId'	=> $application->oauthApplicationId,
			'token'					=> $refreshToken
		];
		if( !( $refresh = $modelRefresh->getByIndices( $indices ) ) )								//  no refresh token found
			$this->errorResponse( 'invalid_request', 'Invalid refresh token.', NULL, 401 );			//  respond error

		$token	= $this->generateAccessToken( $application->oauthApplicationId, $refresh->scope );	//  generate, store and get access token
		$data	= [
			'access_token'	=> $token,
			'token_type'	=> 'bearer',
			'expires_in'	=> $this->lifetimeAccessToken,
			'scope'			=> $refresh->scope,
		];
		if( $this->flagRefreshRefreshToken ){
			$modelRefresh->removeByIndex( 'token', $refreshToken );									//  remove old refresh token
			$refreshToken	= $this->generateRefreshToken( $applicationId, $refresh->scope );		//  generate new refresh token
			$data['refresh_token']	= $refreshToken;												//  append new refresh token to response
		}
		$this->respondJsonData( $data );
	}

	protected function respondJsonData( $data ): never
	{
		header( 'Content-Type: application/json' );
		print( json_encode( $data ) );
		exit;
	}
}
