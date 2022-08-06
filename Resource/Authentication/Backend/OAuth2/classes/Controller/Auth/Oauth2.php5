<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Auth_Oauth2 extends Controller
{
	protected $config;
	protected $session;
	protected $reqest;
	protected $cookie;
	protected $messenger;
	protected $modelProvider;
	protected $modelRelation;

	protected $scopes	= array(
		'adam-paterson/oauth2-slack'	=> ['identity.basic'],
		'stevenmaguire/oauth2-paypal'	=> ['openid', 'profile', 'email', 'phone', 'address'],
		'omines/oauth2-gitlab'			=> ['read_user']
	);

	public function login( $providerId = NULL )
	{
		if( $this->session->has( 'auth_user_id' ) )
			$this->redirectAfterLogin();

		$modelUser		= new Model_User( $this->env );
		$modelRole		= new Model_Role( $this->env );

		$words		= $this->getWords();
		$msgs		= (object) $words['login'];


		if( ( $error = $this->request->get( 'error' ) ) ){
			$currentProviderId	= $this->session->get( 'oauth2_providerId' );
			if( $currentProviderId ){
				$provider	= $this->modelProvider->get( $currentProviderId );
				$this->messenger->noteNotice( $msgs->msgErrorFailed, $provider->title );
			}
			if( $from = $this->session->get( 'oauth2_from' ) )
				$this->restart( $from );
			$this->restart( 'auth/login', FALSE );
		}
		if( ( $code = $this->request->get( 'code' ) ) ){
			$currentProviderId	= $this->session->get( 'oauth2_providerId' );
			$currentState		= $this->session->get( 'oauth2_state' );
			if( !$currentProviderId || !$currentState ){
				$this->messenger->noteFailure( $msgs->msgErrorOauthIncomplete );
				if( $from = $this->session->get( 'oauth2_from' ) )
					$this->restart( $from );
				$this->restart( 'auth/login', FALSE );
			}
			$provider	= $this->modelProvider->get( $currentProviderId );
			if( $currentState !== $this->request->get( 'state' ) ){
				$this->session->remove( 'oauth2_state' );
				$this->messenger->noteFailure( $msgs->msgErrorOauthInvalid );
				if( $from = $this->session->get( 'oauth2_from' ) )
					$this->restart( $from );
				$this->restart( 'auth/login', FALSE );
			}
			try{
				$client	= $this->getProviderObject( $currentProviderId );
				$token	= $client->getAccessToken( 'authorization_code', array( 'code' => $code ) );
				$user	= $client->getResourceOwner( $token );
				$relation		= $this->modelRelation->getByIndices( array(
					'oauthProviderId'	=> $currentProviderId,
					'oauthId'			=> $user->getId(),
				) );
				if( !$relation ){
					$this->messenger->noteError( $msgs->msgErrorNoUserAssigned, $provider->title );
					if( $from = $this->session->get( 'oauth2_from' ) )
						$this->restart( $from );
					$this->restart( 'auth/login', FALSE );
				}
				if( ( $user = $modelUser->get( $relation->localUserId ) ) ){
					$result	= $this->callHook( 'Auth', 'checkBeforeLogin', $this, $data = array(
						'backend'	=> 'oauth2',
						'username'	=> $user ? $user->username : $username,
		//				'password'	=> $password,															//  disabled for security
						'userId'	=> $user ? $user->userId : 0,
					) );
					if( !$this->messenger->gotError() ){
						$role			= $modelRole->get( $user->roleId );
						$allowedRoles	= $this->env->getConfig()->get( 'module.resource_authentication_backend_local.login.roles' );
						$allowedRoles	= explode( ',', $allowedRoles ? $allowedRoles : "*" );

						if( !$role->access )
							$this->messenger->noteError( $msgs->msgRoleLocked, $role->title );
						else if( $allowedRoles !== array( "*" ) && !in_array( $user->roleId, $allowedRoles ) )
							$this->messenger->noteError( $msgs->msgInvalidRole, $role->title );
						else if( $user->status == 0 )
							$this->messenger->noteError( $msgs->msgUserUnconfirmed );
						else if( $user->status == -1 )
							$this->messenger->noteError( $msgs->msgUserLocked );
						else if( $user->status == -2 )
							$this->messenger->noteError( $msgs->msgUserDisabled );
					}
					if( $this->messenger->gotError() ){
						if( $from = $this->session->get( 'oauth2_from' ) )
							$this->restart( $from );
						$this->restart( 'auth/login', FALSE );
					}

					$this->messenger->noteSuccess( $msgs->msgSuccess, $provider->title );
					$this->session->set( 'oauth2_token', $token );
					$this->session->set( 'auth_user_id', $user->userId );
					$this->session->set( 'auth_role_id', $user->roleId );
					$this->logic->setAuthenticatedUser( $user );
					if( $this->request->get( 'login_remember' ) )
						$this->rememberUserInCookie( $user );
					if( $from = $this->session->get( 'oauth2_from' ) ){
						$this->session->remove( 'oauth2_from' );
						$this->restart( $from );
					}
					$this->redirectAfterLogin();
				}
			}
			catch( Exception $e ){
				$this->messenger->noteError( $msgs->msgErrorException, $provider->title, $e->getMessage() );
				if( $this->env->getLog()->logException( $e, $this ) )
					$this->restart( 'auth/local/login', FALSE );
				UI_HTML_Exception_Page::display( $e );
				exit;
			}
		}
		if( $providerId ){
			if( $this->moduleConfig->get( 'loginMode' ) === 'tab' )
				$this->session->set( 'auth_backend', 'Oauth2' );
			$provider	= $this->modelProvider->get( $providerId );
			if( !$provider ){
				$this->messenger->noteError( 'Invalid OAuth2 provider ID.' );
				$this->restart( 'auth/local/login', FALSE );
			}
			$this->session->set( 'oauth2_providerId', $providerId );
			$providerObject	= $this->getProviderObject( $providerId );
			$scopes	= array( $providerObject->getDefaultScopes );
			if( trim( $provider->scopes ) )
				foreach( preg_split( '/\s*,\s/', $provider->scopes ) as $scope )
					if( strlen( trim( $scope ) ) )
						if( !in_array( $scope, $scopes ) )
							$scopes[]	= $scope;
			$authUrl = $providerObject->getAuthorizationUrl( $scopes );
			$this->session->set( 'oauth2_state', $providerObject->getState() );
			$this->session->set( 'oauth2_from', $this->request->get( 'from' ) );
			$this->restart( $authUrl, NULL, NULL, TRUE );
		}
		$providers	= $this->modelProvider->getAll( array(), array( 'rank' => 'ASC' ) );
		$this->addData( 'providers', $providers );
		return;
	}

	public function logout()
	{
		$this->session->remove( 'oauth2_token' );

		$words		= $this->env->getLanguage()->getWords( 'auth' );
		if( $this->session->has( 'auth_user_id' ) ){
			$this->env->getCaptain()->callHook( 'Auth', 'onBeforeLogout', $this, array(
				'userId'	=> $this->session->get( 'auth_user_id' ),
				'roleId'	=> $this->session->get( 'auth_role_id' ),
			) );
			$this->session->remove( 'auth_user_id' );
			$this->session->remove( 'auth_role_id' );
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
		$this->redirectAfterLogout( $redirectController, $redirectAction );
	}

	public function register( $providerId = NULL )
	{
		$modelUser		= new Model_User( $this->env );

		$words		= $this->getWords();
		$msgs		= (object) $words['register'];

		if( ( $error = $this->request->get( 'error' ) ) ){
			$this->messenger->noteError( $error );
			$this->restart( 'auth/local/register', FALSE );
		}
		if( ( $code = $this->request->get( 'code' ) ) ){
			$currentProviderId	= $this->session->get( 'oauth2_providerId' );
			$currentState		= $this->session->get( 'oauth2_state' );
			if( !$currentProviderId || !$currentState ){
				$this->messenger->noteFailure( $msgs->msgErrorOauthIncomplete );
				$this->restart( 'auth/local/register', FALSE );
			}
			$provider	= $this->modelProvider->get( $currentProviderId );
			if( $currentState !== $this->request->get( 'state' ) ){
				$this->session->remove( 'oauth2_state' );
				$this->messenger->noteFailure( $msgs->msgErrorOauthInvalid );
				$this->restart( 'auth/local/register', FALSE );
			}
			try{
				$client		= $this->getProviderObject( $currentProviderId, 'auth/oauth2/register' );
				$token		= $client->getAccessToken( 'authorization_code', array( 'code' => $code ) );
				$user		= $client->getResourceOwner( $token );
//print_m( $user->toArray() );die;
				$provider	= $this->getProvider( $currentProviderId );
				$indices	= array(
					'oauthProviderId'	=> $currentProviderId,
					'oauthId'			=> $user->getId(),
				);
				if( $this->modelRelation->getByIndices( $indices ) ){
					$this->messenger->noteError( $msgs->msgErrorUserAssigned, $provider->title );
					$this->restart( 'auth/local/register', FALSE );
				}
				$this->session->set( 'auth_register_oauth_provider_id', $currentProviderId );
				$this->session->set( 'auth_register_oauth_provider', $provider );
				$this->session->set( 'auth_register_oauth_user_id', $user->getId() );
				$this->session->set( 'auth_register_oauth_data', $user->toArray() );

				$data	= $this->retrieveOwnerDate( $provider, $user );
				foreach( $data as $key => $value )
					$this->session->set( 'auth_register_oauth_'.$key, $value );
				$this->restart( 'auth/local/register', FALSE );
			}
			catch( Exception $e ){
				$this->messenger->noteError( $msgs->msgErrorException, $provider->title, $e->getMessage() );
				$this->messenger->noteError( $e->getMessage() );
				if( $this->env->getLog()->logException( $e, $this ) )
					$this->restart( 'register', TRUE );
				UI_HTML_Exception_Page::display( $e );
				exit;
			}
		}
		if( $providerId ){
			$provider	= $this->modelProvider->get( $providerId );
			if( !$provider ){
				$this->messenger->noteError( 'Invalid OAuth2 provider ID.' );
				$this->restart( 'login', TRUE );
			}
			$this->session->set( 'oauth2_providerId', $providerId );
			$providerObject	= $this->getProviderObject( $providerId, 'auth/oauth2/register' );
			$scopes	= [];
			if( isset( $this->scopes[$provider->composerPackage] ) )
				$scopes	= $this->scopes[$provider->composerPackage];
			$authUrl = $providerObject->getAuthorizationUrl( $scopes );

			$this->session->set( 'oauth2_state', $providerObject->getState() );
			$this->restart( $authUrl, NULL, NULL, TRUE );
		}
		$providers	= $this->modelProvider->getAll( array(), array( 'rank' => 'ASC' ) );
		$this->addData( 'providers', $providers );
		return;
	}

	/**
	 *	...
	 *	@access		public
	 *	@return		void
	 *	@todo		code doc: what is this method doing at all?
	 *	@todo		check where this is used
	 */
	public function unbind()
	{
		$keys	= array_keys( $this->session->getAll( 'auth_register_oauth_' ) );
		foreach( $keys as $key )
			$this->session->remove( 'auth_register_oauth_'.$key );
		if( ( $from = $this->request->get( 'from' ) ) )
			$this->restart( $from, FALSE );
		$this->restart( getEnv( 'HTTP_REFERER' ), FALSE );
	}

	/*  --  PROTECTED --  */

	protected function __onInit()
	{
		$this->config		= $this->env->getConfig();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->cookie		= new Net_HTTP_Cookie( parse_url( $this->env->url, PHP_URL_PATH ) );
		if( isset( $this->env->version ) )
			if( version_compare( $this->env->version, '0.8.6.5', '>=' ) )
				$this->cookie	= $this->env->getCookie();
		$this->cookie			= $this->env->getCookie();
		$this->moduleConfig		= $this->config->getAll( 'module.resource_authentication_backend_oauth2.', TRUE );
		$this->useCsrf			 = $this->env->getModules()->has( 'Security_CSRF' );

		if( !class_exists( 'League\OAuth2\Client\Provider\GenericProvider' ) )
			$this->messenger->noteFailure( '<strong>OAuth2-Client is missing.</strong><br/>Please install package "league/oauth2-client" using composer.' );
		$this->modelProvider	= new Model_Oauth_Provider( $this->env );
		$this->modelRelation	= new Model_Oauth_User( $this->env );

		$this->addData( 'useCsrf', $this->useCsrf );
		$this->refreshToken();
	}

	protected function getProvider( $providerId )
	{
		$provider		= $this->modelProvider->get( $providerId );
		if( !$provider )
			throw new RangeException( 'Invalid provider ID' );
		return $provider;
	}

	protected function getProviderObject( $providerId, $redirectPath = 'auth/oauth2/login' )
	{
		$provider		= $this->modelProvider->get( $providerId );
		if( !$provider )
			throw new RangeException( 'Invalid provider ID' );
		if( !class_exists( $provider->className ) )
			throw new RuntimeException( 'OAuth2 provider class is not existing: '.$provider->className );
		$options		= array(
			'clientId'		=> $provider->clientId,
			'clientSecret'	=> $provider->clientSecret,
			'redirectUri'	=> $this->env->url.$redirectPath,
		);
		if( $provider->options )
			$options	= array_merge( $options, json_decode( $provider->options, TRUE ) );
		return Alg_Object_Factory::createObject( $provider->className, array( $options ) );
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
	protected function redirectAfterLogin( $controller = NULL, $action = NULL )
	{
		if( $controller )																			//  a redirect contoller has been argumented
			$this->restart( $controller.( $action ? '/'.$action : '' ) );							//  redirect to controller and action if given
		$from	= $this->request->get( 'from' );													//  get redirect URL from request if set
//		if( !$from )
//			$from	= $this->session->get( 'oauth2_from' ) );
//		$this->session->remove( 'oauth2_from' ) );
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
	protected function redirectAfterLogout( $controller = NULL, $action = NULL )
	{
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

	protected function refreshToken()
	{
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

	protected function retrieveOwnerDate( $provider, $user )
	{
		$data	= array( 'data' => $user->toArray() );
		if( $provider->composerPackage === 'league/oauth2-facebook' ){
			$data['username']	= $user->getName();
			$data['email']		= $user->getEmail();
			$data['gender']		= $user->getGender() === 'male' ? 2 : 1;
			$data['firstname']	= $user->getFirstName();
			$data['surname']	= $user->getLastName();
		}
		if( $provider->composerPackage === 'league/oauth2-github' ){
			$all	= $user->toArray();
			$name	= preg_split( '/\s+/', $user->getName() );
			$data['username']	= $user->getNickname();
			$data['email']		= $user->getEmail();
			$data['gender']		= 0;
			$data['firstname']	= array_pop( $name );
			$data['surname']	= join( ' ', $name );
			$data['city']		= $all['location'] ?: '';
		}
		if( $provider->composerPackage === 'league/oauth2-google' ){
			$data['username']	= $user->getName();
			$data['email']		= $user->getEmail();
			$data['gender']		= 0;
			$data['firstname']	= $user->getFirstName();
			$data['surname']	= $user->getLastName();
		}
		if( $provider->composerPackage === 'hayageek/oauth2-yahoo' ){
			$data['username']	= $user->getName();
			$data['email']		= $user->getEmail();
			$data['gender']		= 0;
			$data['firstname']	= $user->getFirstName();
			$data['surname']	= $user->getLastName();
		}
		if( $provider->composerPackage === 'stevenmaguire/oauth2-paypal' ){
			$name		= preg_split( '/\s+/', $user->getName() );
			$surname	= array_pop( $name );
			$firstname	= join( ' ', $name );
			$data['username']	= $user->getName();
			$data['email']		= $user->getEmail();
			$data['gender']		= $user->getGender();
			$data['firstname']	= $user->getGivenName() ?: $firstname;
			$data['surname']	= $user->getFamilyName() ?: $surname;
			$address			= $user->getAddress();
			if( isset( $address['street_address'] ) )
				$data['street']	= $address['street_address'];
			if( isset( $address['locality'] ) )
				$data['city']	= $address['locality'];
			if( isset( $address['postal_code'] ) )
				$data['postcode']	= $address['postal_code'];
			if( isset( $address['country'] ) )
				$data['country']	= $address['country'];
			if( isset( $address['phone_number'] ) )
				$data['phone']	= $address['phone_number'];
		}
		if( $provider->composerPackage === 'adam-paterson/oauth2-slack' ){
			$data['username']	= $user->getName();
			$data['email']		= $user->getEmail();
			$data['gender']		= 0;
			$data['firstname']	= $user->getFirstName();
			$data['surname']	= $user->getLastName();
		}
		if( $provider->composerPackage === 'seinopsys/oauth2-deviantart' ){
			$data['username']	= $user->getName();
			$data['email']		= '';
			$data['gender']		= 0;
			$data['firstname']	= '';
			$data['surname']	= '';
		}
		if( $provider->composerPackage === 'omines/oauth2-gitlab' ){
			$all		= $user->toArray();
			$location	= preg_split( '/\s*,\s*/', $all['location'] );
			$name		= preg_split( '/\s+/', $user->getName() );
			$data['username']	= $user->getUsername();
			$data['email']		= $user->getEmail();
			$data['gender']		= 0;
			$data['surname']	= array_pop( $name );
			$data['firstname']	= join( ' ', $name );
			$data['city']		= array_shift( $location );
		}
		return $data;
	}
}
