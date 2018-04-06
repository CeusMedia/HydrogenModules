<?php
class Controller_Auth_Oauth2 extends CMF_Hydrogen_Controller {

	protected $clientId;
	protected $clientSecret;
	protected $clientUri;
	protected $providerUri;
	protected $config;
	protected $session;
	protected $reqest;
	protected $cookie;
	protected $messenger;

	protected function __onInit(){
		$this->config		= $this->env->getConfig();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->cookie		= new Net_HTTP_Cookie( parse_url( $this->env->url, PHP_URL_PATH ) );
		$this->moduleConfig	= $this->config->getAll( 'module.resource_authentication_backend_oauth.', TRUE );
		$this->clientUri	= $this->env->url;
		$this->clientId		= $this->moduleConfig->get( 'provider.client.ID' );
		$this->clientSecret	= $this->moduleConfig->get( 'provider.client.secret' );
		$this->providerUri	= $this->moduleConfig->get( 'provider.URI' );
		$this->addData( 'useCsrf', $this->useCsrf = $this->env->getModules()->has( 'Security_CSRF' ) );
		if( !class_exists( 'League\OAuth2\Client\Provider\GenericProvider' ) )
			$this->messenger->noteFailure( '<strong>OAuth2-Client is missing.</strong><br/>Please install package "league/oauth2-client" using composer.' );

		$this->refreshToken();
	}

	static public function ___onAuthRegisterBackend( CMF_Hydrogen_Environment_Abstract $env, $context, $module, $data = array() ){
		if( $env->getConfig()->get( 'module.resource_authentication_backend_oauth2.enabled' ) ){
			$words	= $env->getLanguage()->getWords( 'auth/oauth2' );
			$context->registerBackend( 'Oauth2', 'oauth2', $words['backend']['title'] );
		}
	}

	static public function ___onAuthRegisterLoginTab( $env, $context, $module, $data = array() ){
		$words		= (object) $env->getLanguage()->getWords( 'auth/oauth2' );						//  load words
		$prefix		= 'module.resource_authentication_backend_oauth2.login.';
		$rank		= $env->getConfig()->get( $prefix.'rank' );
		$label		= $words->login['tab'];
		$context->registerTab( 'auth/oauth2/login', $label, $rank );									//  register main tab
	}

	protected function getProviderObject( $providerId ){
		$modelProvider	= new Model_Oauth_Provider( $this->env );
		$provider		= $modelProvider->get( $providerId );
		if( !$provider )
			throw new RangeException( 'Invalid provider ID' );
		if( !class_exists( $provider->className ) )
			throw new RuntimeException( 'OAuth2 provider class ist not existing: '.$provider->className );
		$options		= array(
			'clientId'		=> $provider->clientId,
			'clientSecret'	=> $provider->clientSecret,
			'redirectUri'	=> $this->env->url.'auth/oauth2/login',
		);
		if( $provider->options )
			$options	= array_merge( $options, json_decode( $provider->options, TRUE ) );
		return Alg_Object_Factory::createObject( $provider->className, array( $options ) );
	}

/*	public function ajaxEmailExists(){
		print( json_encode( NULL ) );
		exit;
	}

	public function ajaxUsernameExists(){
		print( json_encode( NULL ) );
		exit;
	}
*/
/*	public function index(){
		if( $this->request->get( 'error' ) ){
			$messenger	= $this->env->getMessenger();
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
					'redirect_uri'	=> $this->clientUri.'auth/oauth2',
					'code'			=> $this->request->get( 'code' ),
					'state'			=> microtime( TRUE ),
				) );
				$handle	= new Net_CURL();
				$handle->setUrl( $this->providerUri.'/token' );
				$handle->setOption( CURLOPT_POST, TRUE );
				$handle->setOption( CURLOPT_POSTFIELDS, $postData );
				$handle->setOption( CURLOPT_HTTPHEADER, array(
					'Authorization: Basic '.$authorization,
					'Content-Type: application/x-www-form-urlencoded',
					'Content-Length: '.strlen( $postData ),
				) );
				$response	= $handle->exec();
				$response	= json_decode( $response );
				if( !empty( $response->error ) ){
					$error	= $response->error;
					if( !empty( $response->error_description ) )
						$error	= UI_HTML_Tag::create( 'abbr', $error, array(
							'title' => $response->error_description
						) );
					$this->messenger->noteError( $error );
				}
				else{
					$expiresIn	= (int) @$response->expires_in;
					$expiresAt	= $expiresIn ? time() + $expiresIn : time() + 3600;
					$this->session->set( 'oauth2_access_token', $response->access_token );
					$this->session->set( 'oauth2_access_expires_in', $expiresIn );
					$this->session->set( 'oauth2_access_expires_at', $expiresAt );
					$this->session->set( 'oauth2_refresh_token', $response->refresh_token );
					$this->session->set( 'oauth2_scope', $response->scope );

					$modelUser	= new Model_User( $this->env );
					$user 		= $modelUser->getByIndex( 'accountId', $response->user_id );
					if( $user ){
						$this->session->set( 'userId', $user->userId );
						$this->session->set( 'roleId', $user->roleId );
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
							$data->roleId		= $modelRole->getByIndex( 'register', 128, array(), 'roleId' );
							unset( $data->userId );
							$userId				= $modelUser->add( (array) $data );
							$this->session->set( 'userId', $userId );
							$this->session->set( 'roleId', $data->roleId );
							if( $this->request->get( 'login_remember' ) )
								$this->rememberUserInCookie( $user );
						}
					}
//					$from	= $this->request->get( 'from' );										//  get redirect URL from request if set
//					$from	= !preg_match( "/auth\/logout/", $from ) ? $from : '';					//  exclude logout from redirect request
//					$this->restart( './auth?from='.$from );											//  restart (or go to redirect URL)
//					$this->restart( NULL );
				}
			}
			$this->restart( NULL );
		}
	}*/

	public function login( $providerId = NULL ){
		if( $this->session->has( 'userId' ) )
			$this->redirectAfterLogin();

		$modelProvider	= new Model_Oauth_Provider( $this->env );
		$modelUserOauth	= new Model_Oauth_User( $this->env );
		$modelUser		= new Model_User( $this->env );

		$words		= $this->getWords();
		$msgs		= (object) $words['login'];

		if( ( $error = $this->request->get( 'error' ) ) ){
			$this->messenger->noteError( $error );
			$this->restart( 'login', TRUE );
		}
		if( ( $code = $this->request->get( 'code' ) ) ){
			$currentProviderId	= $this->session->get( 'oauth2_providerId' );
			$currentState		= $this->session->get( 'oauth2_state' );
			if( !$currentProviderId || !$currentState ){
				$this->messenger->noteFailure( 'Access denied, no OAuth2 provider selected or not authentication requested.' );
				$this->restart( 'login', TRUE );
			}
			$provider	= $modelProvider->get( $currentProviderId );
			if( $currentState !== $this->request->get( 'state' ) ){
				$this->session->remove( 'oauth2_state' );
				$this->messenger->noteFailure( 'Access denied, invalid OAuth2 state.' );
				$this->restart( 'login', TRUE );
			}
			try{
				$client	= $this->getProviderObject( $currentProviderId );
				$token	= $client->getAccessToken( 'authorization_code', array( 'code' => $code ) );
				$user	= $client->getResourceOwner( $token );
				$localUser		= $modelUserOauth->getByIndices( array(
					'oauthProviderId'	=> $currentProviderId,
					'oauthId'			=> $user->getId(),
				) );
				if( !$localUser ){
					$this->messenger->noteError( $msgs->msgErrorNoUserAssigned, $provider->title );
					$this->restart( 'login', TRUE );
				}
				if( ( $user = $modelUser->get( $localUser->localUserId ) ) ){
					$this->messenger->noteSuccess( $msgs->msgSuccess, $provider->title );
					$this->session->set( 'oauth2_token', $token );
					$this->session->set( 'userId', $user->userId );
					$this->session->set( 'roleId', $user->roleId );
					if( $this->request->get( 'login_remember' ) )
						$this->rememberUserInCookie( $user );
					$this->redirectAfterLogin();
				}
			}
			catch( Exception $e ){
				$this->messenger->noteError( $msgs->msgErrorException, $provider->title, $e->getMessage() );
				if( $this->env->getLog()->logException( $e, $this ) )
					$this->restart( 'login', TRUE );
				UI_HTML_Exception_Page::display( $e );
				exit;
			}
		}
		if( $providerId ){
			$provider	= $modelProvider->get( $providerId );
			if( !$provider ){
				$this->messenger->noteError( 'Invalid OAuth2 provider ID.' );
				$this->restart( 'login', TRUE );
			}
			$this->session->set( 'oauth2_providerId', $providerId );
			$providerObject	= $this->getProviderObject( $providerId );
			$authUrl = $providerObject->getAuthorizationUrl();
			$this->session->set( 'oauth2_state', $providerObject->getState() );
			$this->restart( $authUrl, NULL, NULL, TRUE );
		}
		$providers	= $modelProvider->getAll( array(), array( 'rank' => 'ASC' ) );
		$this->addData( 'providers', $providers );
		return;
	}

	public function logout(){
		$this->session->remove( 'oauth2_token' );

		$words		= $this->env->getLanguage()->getWords( 'auth' );
		if( $this->session->has( 'userId' ) ){
			$this->env->getCaptain()->callHook( 'Auth', 'onBeforeLogout', $this, array(
				'userId'	=> $this->session->get( 'userId' ),
				'roleId'	=> $this->session->get( 'roleId' ),
			) );
			$this->session->remove( 'userId' );
			$this->session->remove( 'roleId' );
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
		$this->redirectAfterLogout( $redirectController, $redirectAction );
	}

	/**
	 *	Dispatch next route after login, by these rules:
	 *	1. Given controller and action
	 *	2. Forced forward path of this auth module
	 *	3. Request paramter 'from'
	 *	4. Forward path of this auth module
	 *	5. Redirect to base auth module index for further dispatching
	 *	ATM this is the same method for each auth module.
	 *	@access		protected
	 *	@return		void
	 *	@todo		find a way to generalize this method into some base auth adapter controller or logic
	 */
	protected function redirectAfterLogin( $controller = NULL, $action = NULL ){
		if( $controller )																			//  a redirect contoller has been argumented
			$this->restart( $controller.( $action ? '/'.$action : '' ) );							//  redirect to controller and action if given
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
	 *	3. Request paramter 'from'
	 *	4. Forward path of this auth module
	 *	5. Go to index (empty path)
	 *	ATM this is the same method for each auth module.
	 *	@access		protected
	 *	@return		void
	 *	@todo		find a way to generalize this method into some base auth adapter controller or logic
	 */
	protected function redirectAfterLogout( $controller = NULL, $action = NULL ){
		if( $controller )																			//  a redirect contoller has been argumented
			$this->restart( $controller.( $action ? '/'.$action : '' ) );							//  redirect to controller and action if given
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
		$this->restart( NULL );																		//  fallback: go to index (empty path)
	}

	protected function refreshToken(){
		$token	= $this->session->get( 'oauth2_token' );
		if( !$token )
			return FALSE;
		if( !$token->getExpires() || !$token->hasExpired() )
			return NULL;
		try{
			$currentProviderId	= $this->session->get( 'oauth2_providerId' );
			$providerObject		= $this->getProviderObject( $currentProviderId );
			$newToken			= $providerObject->getAccessToken( 'refresh_token', array(
				'refresh_token'	=> $token->getRefreshToken(),
			) );
			$this->session->set( 'oauth2_token', $newToken );
			return TRUE;
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $e->getMessage() );
			$this->logout();
		}
	}

	/**
	 *	Tries to relogin user if remembered in cookie.
	 *	Retrieves user ID and password from cookie.
	 *	Checks user, its password and access per role.
	 *	Stores user ID and role ID in session on success.
	 *	Redirects to "from" if given.
	 *	@access		public
	 *	@return		void
	 */
	protected function tryLoginByCookie(){
		if( $this->cookie->get( 'auth_remember' ) ){												//  autologin has been activated
			$userId		= (int) $this->cookie->get( 'auth_remember_id' );							//  get user ID from cookie
			$password	= (string) $this->cookie->get( 'auth_remember_pw' );						//  get hashed password from cookie
			$modelUser	= new Model_User( $this->env );												//  get user model
			$modelRole	= new Model_Role( $this->env );												//  get role model
			if( $userId && $password && ( $user = $modelUser->get( $userId ) ) ){					//  user is existing and password is given
				$role		= $modelRole->get( $user->roleId );										//  get role of user
				if( $role && $role->access ){														//  role exists and allows login
					$passwordMatch	= md5( sha1( $user->password ) ) === $password;					//  compare hashed password with user password
					if( version_compare( PHP_VERSION, '5.5.0' ) >= 0 )								//  for PHP 5.5.0+
						$passwordMatch	= password_verify( $user->password, $password );			//  verify password hash
					if( $passwordMatch ){															//  password from cookie is matching
						$modelUser->edit( $user->userId, array( 'loggedAt' => time() ) );			//  note login time in database
						$this->session->set( 'userId', $user->userId );								//  set user ID in session
						$this->session->set( 'roleId', $user->roleId );								//  set user role in session
						$from	= $this->request->get( 'from' );									//  get redirect URL from request if set
						$from	= !preg_match( "/auth\/logout/", $from ) ? $from : '';				//  exclude logout from redirect request
						$this->restart( './'.$from );												//  restart (or go to redirect URL)
					}
				}
			}
		}
	}
}
