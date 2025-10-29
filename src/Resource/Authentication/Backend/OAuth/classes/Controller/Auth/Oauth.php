<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Validation\Predicates;
use CeusMedia\Common\Net\CURL as NetCurl;
use CeusMedia\Common\Net\HTTP\Cookie as HttpCookie;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;

class Controller_Auth_Oauth extends Controller
{
	protected Dictionary $config;
	protected HttpRequest $request;
	protected Dictionary $session;
	protected MessengerResource $messenger;
	protected HttpCookie $cookie;
	protected Logic_Authentication_Backend_Oauth $logic;

	protected string $clientId;
	protected string $clientSecret;
	protected string $clientUri;
	protected string $providerUri;
	protected bool $useCsrf;
	protected Dictionary $moduleConfigUsers;

/*	public function ajaxEmailExists(){
		print( json_encode( NULL ) );
		exit;
	}

	public function ajaxUsernameExists(){
		print( json_encode( NULL ) );
		exit;
	}
*/
	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function index(): void
	{
//		if( $this->session->get( 'oauth_access_token' ) ){
//		}
//		else{
		if( $this->request->get( 'error' ) ){
			$words		= $this->getWords();
			switch( $this->request->get( 'error' ) ){
				case 'access_denied':
					$this->messenger->noteError( $words['index']['msgAccessDenied'] );
					break;
			}
		}
		else{
			if( $this->request->get( 'code' ) ){
				$authorization	= base64_encode( $this->clientId.':'.$this->clientSecret );
				$postData		= http_build_query( array(
					'grant_type'	=> 'authorization_code',
					'redirect_uri'	=> $this->clientUri.'auth/oauth',
					'code'			=> $this->request->get( 'code' ),
					'state'			=> microtime( TRUE ),
				) );
				$handle	= new NetCurl();
				$handle->setUrl( $this->providerUri.'/token' );
				$handle->setOption( CURLOPT_POST, TRUE );
				$handle->setOption( CURLOPT_POSTFIELDS, $postData );
				$handle->setOption( CURLOPT_HTTPHEADER, [
					'Authorization: Basic '.$authorization,
					'Content-Type: application/x-www-form-urlencoded',
					'Content-Length: '.strlen( $postData ),
				] );
				$response	= $handle->exec();
				$response	= json_decode( $response );
				if( !empty( $response->error ) ){
					$error	= $response->error;
					if( !empty( $response->error_description ) )
						$error	= HtmlTag::create( 'abbr', $error, [
							'title' => $response->error_description
						] );
					$this->messenger->noteError( $error );
				}
				else{
					$expiresIn	= (int) @$response->expires_in;
					$expiresAt	= $expiresIn ? time() + $expiresIn : time() + 3600;
					$this->session->set( 'oauth_access_token', $response->access_token );
					$this->session->set( 'oauth_access_expires_in', $expiresIn );
					$this->session->set( 'oauth_access_expires_at', $expiresAt );
					$this->session->set( 'oauth_refresh_token', $response->refresh_token );
					$this->session->set( 'oauth_scope', $response->scope );

					$modelUser	= new Model_User( $this->env );
					$user 		= $modelUser->getByIndex( 'accountId', $response->user_id );
					if( $user ){
						$this->session->set( Logic_Authentication::$sessionKeyAuthUserId, $user->userId );
						$this->session->set( Logic_Authentication::$sessionKeyAuthRoleId, $user->roleId );
						$this->logic->setAuthenticatedUser( $user );
//						if( $this->request->get( 'login_remember' ) )
//							$this->rememberUserInCookie( $user );
					}
					else{																			//  register new user
						if( $this->env->getModules()->has( 'Resource_Authentication_Backend_Local' ) ){
							$modelRole	= new Model_Role( $this->env );
							$client		= new Resource_Oauth( $this->env );
							$response	= $client->read( 'me' );
							$data		= $response->user;
							$data->accountId	= $data->userId;
							$data->roleId		= $modelRole->getByIndex( 'register', 128, [], 'roleId' );
							unset( $data->userId );
							$userId				= $modelUser->add( (array) $data );
							$this->session->set( Logic_Authentication::$sessionKeyAuthUserId, $userId );
							$this->session->set( Logic_Authentication::$sessionKeyAuthRoleId, $data->roleId );
							$this->logic->setAuthenticatedUser( $modelUser->get( $userId ) );
//							if( $this->request->get( 'login_remember' ) )
//								$this->rememberUserInCookie( $user );
						}
					}
/*					$from	= $this->request->get( 'from' );										//  get redirect URL from request if set
					$from	= !preg_match( "/auth\/logout/", $from ) ? $from : '';					//  exclude logout from redirect request
					$this->restart( './auth?from='.$from );											//  restart (or go to redirect URL)
					$this->restart( NULL );
*/
				}
			}
			$this->restart();
		}
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function login(): void
	{
		if( $this->logic->isAuthenticated() )
			$this->redirectAfterLogin();
		if( $this->moduleConfig->get( 'login.grantType' ) === 'password' ){
			$this->messenger->noteFailure( 'Grant type "password" is not implemented, yet.' );
			$this->restart( NULL, TRUE );
		}
		if( $this->moduleConfig->get( 'login.grantType' ) === 'code' ){
			$params	= http_build_query( array(
				'client_id'		=> $this->clientId,
				'response_type'	=> 'code',
				'redirect_uri'	=> $this->env->url.'auth/oauth',
				'state'			=> microtime( TRUE ),
//				'scope'			=> 'test',
			) );
			$url	= $this->moduleConfig->get( 'provider.URI' ).'/authorize?'.$params;
			$this->restart( $url );
		}

		if( $this->request->getMethod()->isPost() ){
			$authorization	= base64_encode( $this->clientId.':'.$this->clientSecret );
			$postData		= http_build_query( array(
				'grant_type'	=> 'password',
				'username'		=> $this->request->get( 'login_username' ),
				'password'		=> $this->request->get( 'login_password' ),
				'scope'			=> $this->request->get( 'scope' ),
			) );
			$handle	= new NetCurl();
			$handle->setUrl( $this->providerUri.'/token' );
			$handle->setOption( CURLOPT_POST, TRUE );
			$handle->setOption( CURLOPT_POSTFIELDS, $postData );
			$handle->setOption( CURLOPT_HTTPHEADER, [
				'Authorization: Basic '.$authorization,
				'Content-Type: application/x-www-form-urlencoded',
				'Content-Length: '.strlen( $postData ),
			] );
			$response	= $handle->exec();
			$responseData	= json_decode( $response );

			if( $responseData ){
				if( !empty( $responseData->error ) ){
					$error	= $responseData->error;
					if( !empty( $responseData->error_description ) )
						$error	= HtmlTag::create( 'abbr', $error, [
							'title' => $responseData->error_description
						] );
					$this->messenger->noteError( $error );
				}

				else{
					$expiresIn	= (int) @$responseData->expires_in;
					$expiresAt	= $expiresIn ? time() + $expiresIn : time() + 3600;
					$this->session->set( 'oauth_access_token', $responseData->access_token );
					$this->session->set( 'oauth_access_expires_in', $expiresIn );
					$this->session->set( 'oauth_access_expires_at', $expiresAt );
					$this->session->set( 'oauth_refresh_token', $responseData->refresh_token );
					$this->session->set( 'oauth_scope', $responseData->scope );
	//				$modelUser->edit( $user->userId, ['loggedAt' => time() ) );
	//				$this->messenger->noteSuccess( $words->msgSuccess );

					$modelUser	= new Model_User( $this->env );
					/** @var ?Entity_User $user */
					$user = $modelUser->getByIndex( 'username', $this->request->get( 'login_username' ) );
					if( NULL !== $user ){
						$this->session->set( Logic_Authentication::$sessionKeyAuthUserId, $user->userId );
						$this->session->set( Logic_Authentication::$sessionKeyAuthRoleId, $user->roleId );
						$this->logic->setAuthenticatedUser( $user );
//						if( $this->request->get( 'login_remember' ) )
//							$this->rememberUserInCookie( $user );
					}
					else{																			//  register new user
						$modelRole		= new Model_Role( $this->env );
						$client			= new Resource_Oauth( $this->env );
						$path			= 'user/'.$this->request->get( 'login_username' );
						$response		= $client->read( $path );
						$data			= $response->data->user;
						$data['roleId']	= $modelRole->getByIndex( 'register', 128, [], 'roleId' );
						$userId			= $modelUser->add( $data );
						$this->session->set( Logic_Authentication::$sessionKeyAuthUserId, $userId );
						$this->session->set( Logic_Authentication::$sessionKeyAuthRoleId, $data['roleId'] );
						$this->logic->setAuthenticatedUser( $user );
//						if( $this->request->get( 'login_remember' ) )
//							$this->rememberUserInCookie( $user );
					}
					$this->redirectAfterLogin();
				}
			}
			else{
				$this->messenger->noteError( 'Login failed' );
			}
		}
		$this->addData( 'from', $this->request->get( 'from' ) );									//  forward redirect URL to form action
		$this->addData( 'login_username', $this->request->get( 'login_username' ) );
		$this->addData( 'login_remember', (boolean) $this->cookie->get( 'auth_remember' ) );
		$this->addData( 'useRemember', $this->moduleConfig->get( 'login.remember' ) );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function logout(): void
	{
		$this->session->remove( 'oauth_access_token' );
		$this->session->remove( 'oauth_access_expires_in' );
		$this->session->remove( 'oauth_access_expires_at' );
		$this->session->remove( 'oauth_refresh_token' );
		$this->session->remove( 'oauth_scope' );

		$words		= $this->env->getLanguage()->getWords( 'auth' );
		if( $this->logic->isAuthenticated() ){
			$payload	= [
				'userId'	=> $this->logic->getCurrentUserId(),
				'roleId'	=> $this->logic->getCurrentRoleId(),
			];
			$this->env->getCaptain()->callHook( 'Auth', 'onBeforeLogout', $this, $payload );
			$this->logic->clearCurrentUser();

			if( $this->request->has( 'autoLogout' ) ){
				$this->messenger->noteNotice( $words['logout']['msgAutoLogout'] );
			}
			else{
				$this->cookie->remove( 'auth_remember' );
				$this->cookie->remove( 'auth_remember_id' );
				$this->cookie->remove( 'auth_remember_pw' );
				$this->messenger->noteSuccess( $words['logout']['msgSuccess'] );
			}
//			if( $this->moduleConfig->get( 'logout.clearSession' ) )									//  session is to be cleared on logout
//				session_destroy();																	//  completely destroy session
		}
		$redirectController	= NULL;
		$redirectAction		= NULL;
		$this->redirectAfterLogout( $redirectController, $redirectAction );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->config		= $this->env->getConfig();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->cookie		= new HttpCookie( parse_url( $this->env->url, PHP_URL_PATH ) );
		if( isset( $this->env->version ) )
			if( version_compare( $this->env->version, '0.8.6.5', '>=' ) )
				$this->cookie	= $this->env->getCookie();
		$this->moduleConfig			= $this->config->getAll( 'module.resource_authentication_backend_oauth.', TRUE );
		$this->moduleConfigUsers	= $this->config->getAll( 'module.resource_users.', TRUE );
		$this->clientUri	= $this->env->url;
		$this->clientId		= $this->moduleConfig->get( 'provider.client.ID' );
		$this->clientSecret	= $this->moduleConfig->get( 'provider.client.secret' );
		$this->providerUri	= $this->moduleConfig->get( 'provider.URI' );
		$this->logic		= Logic_Authentication_Backend_Oauth::getInstance( $this->env );
		$this->useCsrf		= $this->env->getModules()->has( 'Security_CSRF' );
		$this->addData( 'useCsrf', $this->useCsrf );
		$this->refreshToken();
	}

	/**
	 *	Dispatch next route after login, by these rules:
	 *	1. Given controller and action
	 *	2. Forced forward path of this auth module
	 *	3. Request parameter 'from'
	 *	4. Forward path of this auth module
	 *	5. Redirect to base auth module index for further dispatching
	 *	ATM this is the same method for each auth module.
	 *	@access		protected
	 *	@param		?string		$controller
	 *	@param		?string		$action
	 *	@return		void
	 *	@todo		find a way to generalize this method into some base auth adapter controller or logic
	 */
	protected function redirectAfterLogin( ?string $controller = NULL, ?string $action = NULL ): void
	{
		$controller	= trim( $controller ?? '' );
		$action		= trim( $action ?? '' );
		if( '' !== $controller )																	//  a redirect controller has been given
			$this->restart( $controller.( '' !== $action ? '/'.$action : '' ) );					//  redirect to controller and action if given
		$from	= $this->request->get( 'from' );													//  get redirect URL from request if set
		$from	= !preg_match( "/auth\/logout/", $from ) ? $from : '';								//  exclude logout from redirect request
		$from	= preg_replace( "/^index\/index\/?/", "", $from );									//  remove full index path from redirect request
		$forwardPath	= $this->moduleConfig->get( 'login.forward.path' );							//  get forward path for this module
		$forwardForce	= $this->moduleConfig->get( 'login.forward.force' );						//  check if forwarding is forced
		if( $forwardPath && $forwardForce )															//  forward path given and forced
			$this->restart( $forwardPath.( $from ? '?from='.$from : '' ) );							//  redirect to forced forward path of this auth module
		if( $from )																					//  redirect target is given
			$this->restart( 'auth?from='.$from );													//  carry redirect to base auth module dispatcher
		if( $forwardPath )																			//  fallback: forward path given
			$this->restart( $forwardPath );															//  redirect to forward path of this auth module
		$this->restart( 'auth' );																	//  fallback: redirect to base auth module dispatcher
	}

	/**
	 *	Dispatch next route after logout, by these rules:
	 *	1. Given controller and action
	 *	2. Forced forward path of this auth module
	 *	3. Request parameter 'from'
	 *	4. Forward path of this auth module
	 *	5. Go to index (empty path)
	 *	ATM this is the same method for each auth module.
	 *	@access		protected
	 *	@param		?string		$controller
	 *	@param		?string		$action
	 *	@return		void
	 *	@todo		find a way to generalize this method into some base auth adapter controller or logic
	 */
	protected function redirectAfterLogout( ?string $controller = NULL, ?string $action = NULL ): void
	{
		$controller	= trim( $controller ?? '' );
		$action		= trim( $action ?? '' );
		if( '' !== $controller )																	//  a redirect controller has been given
			$this->restart( $controller.( '' !== $action ? '/'.$action : '' ) );					//  redirect to controller and action if given
		$from	= $this->request->get( 'from' );													//  get redirect URL from request if set
//		$from	= !preg_match( "/auth\/logout/", $from ) ? $from : '';								//  exclude logout from redirect request
		$from	= preg_replace( "/^index\/index\/?/", "", $from );									//  remove full index path from redirect request
		$forwardPath	= $this->moduleConfig->get( 'logout.forward.path' );						//  get forward path for this module
		$forwardForce	= $this->moduleConfig->get( 'logout.forward.force' );						//  check if forwarding is forced
		if( $forwardPath && $forwardForce )															//  forward path given and forced
			$this->restart( $forwardPath.( $from ? '?from='.$from : '' ) );							//  redirect to forced forward path of this auth module
		if( $from )																					//  redirect target is given
			$this->restart( 'auth?from='.$from );													//  carry redirect to base auth module dispatcher
		if( $forwardPath )																			//  fallback: forward path given
			$this->restart( $forwardPath );															//  redirect to forward path of this auth module
		$this->restart();																				//  fallback: go to index (empty path)
	}

	/**
	 *	@return		int
	 *	@throws		ReflectionException
	 */
	protected function evaluateRoleIdOnRegister(): int
	{
		$modelRole	= new Model_Role( $this->env );
		$input		= $this->request->getAllFromSource( 'POST', TRUE );
		$words		= (object) $this->getWords( 'register' );

		$roleDefault	= $modelRole->getByIndex( 'register', 128 );
		if( !$roleDefault ){
			$this->messenger->noteFailure( $words->msgNoDefaultRoleDefined );
			$from	= $this->request->get( 'from' );
			$this->restart( $from ?: NULL, !$from );
		}

		$rolesAllowed	= [];
		foreach( $modelRole->getAllByIndex( 'register', [64, 128] ) as $role )
			$rolesAllowed[]	= $role->roleId;
		$roleId		= $roleDefault->roleId;															//  use default register role if none given

		if( 0 !== strlen( trim( $input->get( 'roleId', '' ) ) ) )
			if( in_array( (int) $input->get( 'roleId' ), $rolesAllowed ) )
				$roleId		= $input->get( 'roleId' );
		if( !in_array( $roleId, $rolesAllowed ) ){
			$this->messenger->noteError( $words->msgRoleInvalid );
			$this->restart( 'register', TRUE );
		}
		return $roleId;
	}

	/**
	 *	@throws		ReflectionException
	 */
	protected function evaluateInputOnRegister(): Dictionary|FALSE
	{
		$modelUser	= new Model_User( $this->env );
		$words		= (object) $this->getWords( 'register' );
		$options	= $this->moduleConfigUsers;
		$input		= $this->request->getAllFromSource( 'POST', TRUE );

		$input->set( 'roleId', $this->evaluateRoleIdOnRegister() );

		$nameMinLength	= $options->get( 'name.length.min' );
		$nameMaxLength	= $options->get( 'name.length.max' );
		$nameRegExp		= $options->get( 'name.preg' );
		$pwdMinLength	= $options->get( 'password.length.min' );
		$needsEmail		= $options->get( 'email.mandatory' );
		$needsFirstname	= $options->get( 'firstname.mandatory' );
		$needsSurname	= $options->get( 'surname.mandatory' );
		$needsTac		= $options->get( 'tac.mandatory' );

		$input->set( 'username', trim( $input->get( 'username', '' ) ) );
		$input->set( 'password', trim( $input->get( 'password' ) ) );
		$input->set( 'email', trim( $input->get( 'email' ) ) );
		$input->set( 'firstname', trim( $input->get( 'firstname', '' ) ) );
		$input->set( 'surname', trim( $input->get( 'surname' ) ) );

		$payload	= $input->getAll();
		$this->callHook( 'Auth', 'checkBeforeRegister', $this, $payload );
		$input	= new Dictionary( $payload );

		if( '' === $input->get( 'username' ) ){
			$this->messenger->noteError( $words->msgNoUsername );
			return FALSE;
		}
		if( $modelUser->countByIndex( 'username', $input->get( 'username' ) ) ){
			$this->messenger->noteError( $words->msgUsernameExisting, $input->get( 'username' ) );
			return FALSE;
		}
		if( $nameRegExp && !Predicates::isPreg( $input->get( 'username' ), $nameRegExp ) ){
			$this->messenger->noteError( $words->msgUsernameInvalid, $input->get( 'username' ), $nameRegExp );
			return FALSE;
		}
		if( '' === $input->get( 'password' ) ){
			$this->messenger->noteError( $words->msgNoPassword );
			return FALSE;
		}
		if( $pwdMinLength && strlen( $input->get( 'password' ) ) < $pwdMinLength ){
			$this->messenger->noteError( $words->msgPasswordTooShort, $pwdMinLength );
			return FALSE;
		}
		if( $needsEmail && '' === $input->get( 'email' ) ){
			$this->messenger->noteError( $words->msgNoEmail);
			return FALSE;
		}
		if( '' !== $input->get( 'email' ) && $modelUser->countByIndex( 'email', $input->get( 'email' ) ) ){
			$this->messenger->noteError( $words->msgEmailExisting, $input->get( 'email' ) );
			return FALSE;
		}
		if( $needsFirstname && '' === $input->get( 'firstname' ) ){
			$this->messenger->noteError( $words->msgNoFirstname );
			return FALSE;
		}
		if( $needsSurname && '' === $input->get( 'surname' ) ){
			$this->messenger->noteError( $words->msgNoSurname );
			return FALSE;
		}
		if( $needsTac && empty( $input['accept_tac'] ) ){
			$this->messenger->noteError( $words->msgTermsNotAccepted );
			return FALSE;
		}
		return $input;
	}

	/**
	 *	@param		Dictionary		$input
	 *	@param		int|string		$userId
	 *	@param		int				$status
	 *	@param		?string			$from
	 *	@throws		ReflectionException
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	protected function sendRegisterMail( Dictionary $input, int|string $userId, int $status, ?string $from ): void
	{
		if( Model_User::STATUS_UNCONFIRMED === $status )
			return;
		$modelUser	= new Model_User( $this->env );
		$options	= $this->moduleConfigUsers;
		$status			= (int) $options->get( 'status.register' );
		$passwordPepper	= trim( $options->get( 'password.pepper' ) );								//  string to pepper password with

		$data				= $input->getAll();
		$data['from']		= $from;
		$data['pak']		= md5( 'pak:'.$userId.'/'.$input->get( 'username' ).'&'.$passwordPepper );

		$language	= $this->env->getLanguage()->getLanguage();
		/** @var Entity_User $user */
		$user		= $modelUser->get( $userId );
		$mail		= new Mail_Auth_Local_Register( $this->env, $data );
		$logic		= Logic_Mail::getInstance( $this->env );
		$logic->appendRegisteredAttachments( $mail, $language );
		$mailId		= $logic->enqueueMail( $mail, $language, $user );
		$logic->sendQueuedMail( $mailId );
	}

	/**
	 *	@throws		SimpleCacheInvalidArgumentException
	 *	@throws		ReflectionException
	 */
	protected function linkCreatedAccountToOAuth( $userId ): void
	{
		if( !$this->session->get( 'auth_register_oauth_user_id' ) )
			return;
		$modelOauthUser	= new Model_Oauth_User( $this->env );
		$modelOauthUser->add( [
			'oauthProviderId'	=> $this->session->get( 'auth_register_oauth_provider_id' ),
			'oauthId'			=> $this->session->get( 'auth_register_oauth_user_id' ),
			'localUserId'		=> $userId,
			'timestamp'			=> time(),
		] );
		$this->session->remove( 'auth_register_oauth_provider_id' );
		$this->session->remove( 'auth_register_oauth_provider' );
		$this->session->remove( 'auth_register_oauth_user_id' );
		$this->session->remove( 'auth_register_oauth_data' );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function refreshToken(): void
	{
		if( $this->session->get( 'oauth_access_token' ) ){
			if( time() >= $this->session->get( 'oauth_access_expires_at' ) ){
				if( $this->session->get( 'oauth_refresh_token' ) ){
					$authorization	= base64_encode( $this->clientId.':'.$this->clientSecret );
					$postData		= http_build_query( array(
						'grant_type'	=> 'refresh_token',
						'refresh_token'	=> $this->session->get( 'oauth_refresh_token' ),
					) );
					$handle	= new NetCurl();
					$handle->setUrl( $this->providerUri.'/token' );
					$handle->setOption( CURLOPT_POST, TRUE );
					$handle->setOption( CURLOPT_POSTFIELDS, $postData );
					$handle->setOption( CURLOPT_HTTPHEADER, [
						'Authorization: Basic '.$authorization,
						'Content-Type: application/x-www-form-urlencoded',
						'Content-Length: '.strlen( $postData ),
					] );
					$response	= $handle->exec();
					$response	= json_decode( $response );
					if( !empty( $response->error ) ){
						$error	= $response->error;
						if( !empty( $response->error_description ) )
							$error	= HtmlTag::create( 'abbr', $error, [
								'title' => $response->error_description
							] );
						$this->messenger->noteError( $error );
						$this->logout();
					}
					$expiresIn	= (int) @$response->expires_in;
					$expiresAt	= $expiresIn ? time() + $expiresIn : time() + 3600;
					$this->session->set( 'oauth_access_token', $response->access_token );
					$this->session->set( 'oauth_access_expires_in', $expiresIn );
					$this->session->set( 'oauth_access_expires_at', $expiresAt );
					$this->session->set( 'oauth_scope', $response->scope );
					if( !empty( $response->refresh_token ) )
						$this->session->set( 'oauth_refresh_token', $response->refresh_token );
				}
				else
					$this->logout();
			}
/*			$this->setData( array(
				'inside'	=> TRUE,
				'token'		=> $this->session->get( 'oauth_access_token' ),
				'expiresIn'	=> $this->session->get( 'oauth_access_expires_at' ) - time(),
			) );*/
		}
	}

	/**
	 *	Tries to re-login user if remembered in cookie.
	 *	Retrieves user ID and password from cookie.
	 *	Checks user, its password and access per role.
	 *	Stores user ID and role ID in session on success.
	 *	Redirects to "from" if given.
	 *	@access		public
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	protected function tryLoginByCookie(): void
	{
		if( !$this->logic->isAuthenticated() )
			return;
		if( !$this->cookie->get( 'auth_remember' ) )											//  autologin has not been activated
			return;

		$userId			= (int) $this->cookie->get( 'auth_remember_id' );						//  get user ID from cookie
		$passwordHash	= (string) $this->cookie->get( 'auth_remember_pw' );					//  get hashed password from cookie
		if( 0 === $userId || '' === $passwordHash )													//  missing user ID or password
			return;

		$modelUser	= new Model_User( $this->env );													//  get user model
		/** @var ?Entity_User $user */
		$user		= $modelUser->get( $userId );													//  get user entity

		if( NULL !== $user ){ 																		//  user is NOT existing
			$role	= Logic_Role::getInstance( $this->env )->get( $user->roleId );					//  get role of user
			if(																						//  extended IF AND
				NULL !== $role &&																	//  - role exists
				0 !== $role->access	&&																//  - role allows login
				password_verify( $user->password, $passwordHash )									//  - password matche with stored hash
			){
				$modelUser->edit( $user->userId, ['loggedAt' => time()] );							//  note login time in database
				$this->session->set( Logic_Authentication::$sessionKeyAuthUserId, $user->userId );	//  set user ID in session
				$this->session->set( Logic_Authentication::$sessionKeyAuthRoleId, $user->roleId );	//  set user role in session
				$this->logic->setAuthenticatedUser( $user );
				$from	= $this->request->get( 'from' );										//  get redirect URL from request if set
				$from	= str_replace( "index/index", "", $from );							//  shortcut index
				$from	= !preg_match( "/auth\/logout/", $from ) ? $from : '';				//  exclude logout from redirect request
				$this->restart( './'.$from );													//  restart (or go to redirect URL)
			}
		}
		$this->cookie->remove( 'auth_remember' );
		$this->cookie->remove( 'auth_remember_id' );
		$this->cookie->remove( 'auth_remember_pw' );
	}
}
