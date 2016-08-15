<?php
class Controller_Auth_Oauth extends CMF_Hydrogen_Controller {

	protected $clientId;
	protected $clientSecret;
	protected $clientUri;
	protected $providerUri;
	protected $config;
	protected $session;
	protected $reqest;
	protected $cookie;

	protected function __onInit(){
		$this->config		= $this->env->getConfig();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->cookie		= new Net_HTTP_Cookie( parse_url( $this->env->url, PHP_URL_PATH ) );
		$this->moduleConfig	= $this->config->getAll( 'module.resource_authentication_backend_oauth.', TRUE );
		$this->clientUri	= $this->env->url;
		$this->clientId		= $this->moduleConfig->get( 'provider.client.ID' );
		$this->clientSecret	= $this->moduleConfig->get( 'provider.client.secret' );
		$this->providerUri	= $this->moduleConfig->get( 'provider.URI' );
		$this->addData( 'useCsrf', $this->useCsrf = $this->env->getModules()->has( 'Security_CSRF' ) );

		$this->refreshToken();
	}

	static public function ___onAuthRegisterBackend( CMF_Hydrogen_Environment_Abstract $env, $context, $module, $data = array() ){
		if( $env->getConfig()->get( 'module.resource_authentication_backend_oauth.enabled' ) )
			$context->registerBackend( 'Oauth' );
	}

	public function index(){
		$messenger		= $this->env->getMessenger();

//		if( $this->session->get( 'oauth_access_token' ) ){
//		}
//		else{
		if( $this->request->get( 'error' ) ){
			$messenger	= $this->env->getMessenger();
			$words		= $this->getWords();
			switch( $this->request->get( 'error' ) ){
				case 'access_denied':
					$messenger->noteError( $words['index']['msgAccessDenied'] );
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
					$messenger->noteError( $error );
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
						$this->session->set( 'userId', $user->userId );
						$this->session->set( 'roleId', $user->roleId );
//						if( $this->request->get( 'login_remember' ) )
//							$this->rememberUserInCookie( $user );
					}
					else{																			//  register new user
						$modelRole	= new Model_Role( $this->env );
						$client		= new Resource_Oauth( $this->env );
						$response	= $client->read( 'me' );
						$data		= $response->user;
						$data->accountId	= $data->userId;
						$data->roleId		= $modelRole->getByIndex( 'register', 128, 'roleId' );
						unset( $data->userId );
						$userId				= $modelUser->add( (array) $data );
						$this->session->set( 'userId', $userId );
						$this->session->set( 'roleId', $data->roleId );
						if( $this->request->get( 'login_remember' ) )
							$this->rememberUserInCookie( $user );
					}
/*					$from	= $this->request->get( 'from' );										//  get redirect URL from request if set
					$from	= !preg_match( "/auth\/logout/", $from ) ? $from : '';					//  exclude logout from redirect request
					$this->restart( './auth?from='.$from );											//  restart (or go to redirect URL)
					$this->restart( NULL );
*/
					$this->restart( NULL );
				}
			}
		}
	}

	public function login(){
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

		$messenger		= $this->env->getMessenger();

		if( $this->request->getMethod() == "POST" ){
			$authorization	= base64_encode( $this->clientId.':'.$this->clientSecret );
			$postData		= http_build_query( array(
				'grant_type'	=> 'password',
				'username'		=> $this->request->get( 'login_username' ),
				'password'		=> $this->request->get( 'login_password' ),
				'scope'			=> $this->request->get( 'scope' ),
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
			$responseData	= json_decode( $response );

			if( $responseData ){
				if( !empty( $responseData->error ) ){
					$error	= $responseData->error;
					if( !empty( $responseData->error_description ) )
						$error	= UI_HTML_Tag::create( 'abbr', $error, array(
							'title' => $responseData->error_description
						) );
					$messenger->noteError( $error );
				}

				else{
					$expiresIn	= (int) @$responseData->expires_in;
					$expiresAt	= $expiresIn ? time() + $expiresIn : time() + 3600;
					$this->session->set( 'oauth_access_token', $responseData->access_token );
					$this->session->set( 'oauth_access_expires_in', $expiresIn );
					$this->session->set( 'oauth_access_expires_at', $expiresAt );
					$this->session->set( 'oauth_refresh_token', $responseData->refresh_token );
					$this->session->set( 'oauth_scope', $responseData->scope );
	//				$modelUser->edit( $user->userId, array( 'loggedAt' => time() ) );
	//				$this->messenger->noteSuccess( $words->msgSuccess );

					$modelUser	= new Model_User( $this->env );
					$user = $modelUser->getByIndex( 'username', $this->request->get( 'login_username' ) );
					if( $user ){
						$this->session->set( 'userId', $user->userId );
						$this->session->set( 'roleId', $user->roleId );
						if( $this->request->get( 'login_remember' ) )
							$this->rememberUserInCookie( $user );
					}
					else{																			//  register new user
						$modelRole		= new Model_Role( $this->env );
						$client			= new Resource_Oauth( $this->env );
						$path			= 'user/'.$this->request->get( 'login_username' );
						$response		= $client->read( $path );
						$data			= $response->data->user;
						$data['roleId']	= $modelRole->getByIndex( 'register', 128, 'roleId' );
						$userId			= $modelUser->add( $data );
						$this->session->set( 'userId', $userId );
						$this->session->set( 'roleId', $roleId );
						if( $this->request->get( 'login_remember' ) )
							$this->rememberUserInCookie( $user );
					}
					$from	= $this->request->get( 'from' );										//  get redirect URL from request if set
					$from	= !preg_match( "/auth\/logout/", $from ) ? $from : '';					//  exclude logout from redirect request
					$this->restart( './auth?from='.$from );											//  restart (or go to redirect URL)
					$this->restart( NULL );
				}
			}
			else{
				$messenger->noteError( 'Login failed' );
			}
		}
		$this->addData( 'from', $this->request->get( 'from' ) );									//  forward redirect URL to form action
		$this->addData( 'login_username', $this->request->get( 'login_username' ) );
		$this->addData( 'login_remember', (boolean) $this->cookie->get( 'auth_remember' ) );
		$this->addData( 'useRegister', $this->moduleConfig->get( 'register' ) );
		$this->addData( 'useRemember', $this->moduleConfig->get( 'login.remember' ) );
	}

	public function logout(){
		$this->session->remove( 'oauth_access_token' );
		$this->session->remove( 'oauth_access_expires_in' );
		$this->session->remove( 'oauth_access_expires_at' );
		$this->session->remove( 'oauth_refresh_token' );
		$this->session->remove( 'oauth_scope' );

		$words		= $this->env->getLanguage()->getWords( 'auth' );
		if( $this->session->remove( 'userId' ) ){
			$this->session->remove( 'userId' );
			$this->session->remove( 'roleId' );
			if( $this->request->has( 'autoLogout' ) ){
				$this->env->getMessenger()->noteNotice( $words['logout']['msgAutoLogout'] );
			}
			else{
				$this->cookie->remove( 'auth_remember' );
				$this->cookie->remove( 'auth_remember_id' );
				$this->cookie->remove( 'auth_remember_pw' );
				$this->env->getMessenger()->noteSuccess( $words['logout']['msgSuccess'] );
			}
//			if( $this->moduleConfig->get( 'logout.clearSession' ) )									//  session is to be cleared on logout
//				session_destroy();																	//  completely destroy session
		}

		$from			= $this->request->get( 'from' );
		$forwardPath	= $this->moduleConfig->get( 'logout.forward.path' );
		$forwardForce	= $this->moduleConfig->get( 'logout.forward.force' );

		if( $forwardPath && $forwardForce )
			$this->restart( $forwardPath.( $from ? '?from='.$from : '' ) );
		if( $from )
			$this->restart( $from );
		if( $forwardPath )
			$this->restart( $forwardPath.( $from ? '?from='.$from : '' ) );

		$redirectTo	= $redirectController.( $redirectAction ? '/'.$redirectAction : '' );
		$this->restart( $redirectTo );															//  restart (to redirect URL if set)
	}

	protected function refreshToken(){
		$messenger		= $this->env->getMessenger();

		if( $this->session->get( 'oauth_access_token' ) ){
			if( time() >= $this->session->get( 'oauth_access_expires_at' ) ){
				if( $this->session->get( 'oauth_refresh_token' ) ){
					$authorization	= base64_encode( $this->clientId.':'.$this->clientSecret );
					$postData		= http_build_query( array(
						'grant_type'	=> 'refresh_token',
						'refresh_token'	=> $this->session->get( 'oauth_refresh_token' ),
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
						$messenger->noteError( $error );
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
