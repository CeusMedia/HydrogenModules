<?php
class Controller_Auth_Rest extends CMF_Hydrogen_Controller {

	protected $config;
	protected $request;
	protected $session;
	protected $cookie;
	protected $messenger;
	protected $useCsrf;
	protected $moduleConfig;

	public function __onInit(){
		$this->config		= $this->env->getConfig();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
//		$this->cookie		= new Net_HTTP_PartitionCookie( "hydrogen", "/" );
		$this->cookie		= new Net_HTTP_Cookie( parse_url( $this->env->url, PHP_URL_PATH ) );
		$this->messenger	= $this->env->getMessenger();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_authentication_backend_rest.', TRUE );
		$this->addData( 'useCsrf', $this->useCsrf = $this->env->getModules()->has( 'Security_CSRF' ) );
		$this->logic		= Logic_Authentication_Backend_Rest::getInstance( $this->env );
	}

/*	static public function ___onPageApplyModules( CMF_Hydrogen_Environment_Abstract $env, $context, $module, $data = array() ){
		$userId		= (int) $env->getSession()->get( 'userId' );														//  get ID of current user (or zero)
		$cookie		= new Net_HTTP_Cookie( parse_url( $env->url, PHP_URL_PATH ) );
//		$remember	= (bool) $cookie->get( 'auth_remember' );
//		$env->getSession()->set( 'isRemembered', $remember );
		$script		= 'Auth.init('.$userId.', false);';											//  initialize Auth class with user ID
		$env->getPage()->js->addScriptOnReady( $script, 1 );															//  enlist script to be run on ready
	}
*/
	static public function ___onAuthRegisterBackend( CMF_Hydrogen_Environment_Abstract $env, $context, $module, $data = array() ){
		if( $env->getConfig()->get( 'module.resource_authentication_backend_rest.enabled' ) ){
			$words	= $env->getLanguage()->getWords( 'auth/rest' );
			$context->registerBackend( 'Rest', 'rest', $words['backend']['title'] );
		}
	}

	static public function ___onAuthRegisterLoginTab( $env, $context, $module, $data = array() ){
		$words		= (object) $env->getLanguage()->getWords( 'auth/rest' );						//  load words
		$prefix		= 'module.resource_authentication_backend_rest.login.';
		$rank		= $env->getConfig()->get( $prefix.'rank' );
		$label		= $words->login['tab'];
		$context->registerTab( 'auth/rest/login', $label, $rank );									//  register main tab
	}

	public function ajaxUsernameExists(){
		$username	= trim( $this->request->get( 'username' ) );
		$result		= $this->logic->checkUsername( $username );
		print( json_encode( $result ) );
		exit;
	}

	public function ajaxEmailExists(){
		$email		= trim( $this->request->get( 'email' ) );
		$result		= $this->logic->checkEmail( $email );
		print( json_encode( $result ) );
		exit;
	}

	public function ajaxPasswordStrength(){
		$password	= trim( $this->request->get( 'password' ) );
		$result		= 0;
		if( strlen( $password ) ){
			$result			= Alg_Crypt_PasswordStrength::getStrength( $password );
		}
		print( json_encode( $result ) );
		exit;
	}

	/**
	 *	Check given user password against old and newer password storage.
	 *	If newer password store is supported and old password has been found, migration will apply.
	 *
	 *	@access		protected
	 *	@param   	object   	$user		User data object
	 *	@param   	string		$password	Password to check on login
	 *	@todo   	clean up if support for old passwort decays
	 *	@todo   	reintegrate cleansed lines into login method (if this makes sense)
	 */
	protected function checkPasswordOnLogin( $user, $password ){
		return $this->logic->checkPassword( $username, $password );
	}

	public function index(){
		if( !$this->session->has( 'userId' ) )
			return $this->redirect( 'auth/rest', 'login' );

		$from			= $this->request->get( 'from' );
		$forwardPath	= $this->moduleConfig->get( 'login.forward.path' );
		$forwardForce	= $this->moduleConfig->get( 'login.forward.force' );

		if( $forwardPath && $forwardForce )
			$this->restart( $forwardPath.( $from ? '?from='.$from : '' ) );
		if( $from )
			return $this->restart( $from );
		if( $forwardPath )
			$this->restart( $forwardPath.( $from ? '?from='.$from : '' ) );
		return $this->restart( NULL );
	}

	public function confirm( $userId, $token ){
		$words		= (object) $this->getWords( 'confirm' );
		$result		= $this->logic->confirm( $userId, $token );
		if( is_object( $result ) ){
			$this->messenger->noteSuccess( $words->msgSuccess );
			$this->restart( 'login/'.$result->data->username, TRUE );
		}
		if( $result == -1 )
			$this->messenger->noteError( $words->msgNoUserId );
		if( $result == -2 )
			$this->messenger->noteError( $words->msgNoToken );
		if( $result == -10 )
			$this->messenger->noteError( $words->msgInvalidToken );
		else if( $result == -11 )
			$this->messenger->noteError( $words->msgAccountAlreadyConfirmed );
		else if( $result == -12 )
			$this->messenger->noteError( $words->msgManagerAlreadyConfirmed );
		$this->restart( 'login', TRUE );
	}

	public function login( $username = NULL ){
		if( $this->session->has( 'userId' ) ){
			if( $this->request->has( 'from' ) )
				$this->restart( $from );
			$this->restart( NULL, TRUE );
		}

//		$this->tryLoginByCookie();
		$words		= (object) $this->getWords( 'login' );

		if( $this->request->has( 'doLogin' ) ) {

			if( $this->useCsrf ){
				$controller	= new Controller_Csrf( $this->env );
				$controller->checkToken();
			}
			if( !trim( $username = $this->request->get( 'login_username' ) ) )
				$this->messenger->noteError( $words->msgNoUsername );
			else if( !trim( $password = $this->request->get( 'login_password' ) ) )
				$this->messenger->noteError( $words->msgNoPassword );
			else{
				$user	= $this->logic->checkPassword( $username, $password );
				if( isset( $user->data->userId ) ){
					$this->messenger->noteSuccess( $words->msgSuccess );
					$this->session->set( 'accountId', $user->data->accountId );
					$this->session->set( 'userId', $user->data->userId );
					$this->session->set( 'roleId', $user->data->roleId );
					$this->session->set( 'token', $user->data->token );
					$this->session->set( 'rights', $user->data->rights );
					$this->session->set( 'authBackend', 'Rest' );
	//				if( $this->request->get( 'login_remember' ) )
	//					$this->rememberUserInCookie( $user->userId, $password );
					$from	= $this->request->get( 'from' );									//  get redirect URL from request if set
					$from	= !preg_match( "/auth\/logout/", $from ) ? $from : '';				//  exclude logout from redirect request
					$this->restart( './auth/rest?from='.$from );								//  restart (or go to redirect URL)
				}
				if( $user->data == -1 )
					$this->messenger->noteError( $words->msgNoUsername );
				if( $user->data == -2 )
					$this->messenger->noteError( $words->msgNoPassword );
				if( $user->data == -10 )
					$this->messenger->noteError( $words->msgInvalidDomain );
				else if( $user->data == -11 )
					$this->messenger->noteError( $words->msgDomainDisabled );
				else if( $user->data == -20 )
					$this->messenger->noteError( $words->msgInvalidUser );
				else if( $user->data == -21 )
					$this->messenger->noteError( $words->msgUserDisabled );
				else if( $user->data == -22)
					$this->messenger->noteError( $words->msgUserUnconfirmed );
				else if( $user->data == -30 )
					$this->messenger->noteError( $words->msgInvalidPassword );
			}


/*					$result	= $this->callHook( 'Auth', 'checkBeforeLogin', $this, $data = array(
						'username'	=> $user ? $user->username : $username,
		//				'password'	=> $password,															//  disabled for security
						'userId'	=> $user ? $user->userId : 0,
					) );*/
		}
//		$this->cookie->remove( 'auth_remember' );
		$this->addData( 'from', $this->request->get( 'from' ) );									//  forward redirect URL to form action
		$this->addData( 'login_username', $username );
//		$this->addData( 'login_remember', (boolean) $this->cookie->get( 'auth_remember' ) );
		$this->addData( 'useRegister', $this->moduleConfig->get( 'register' ) );
//		$this->addData( 'useRemember', $this->moduleConfig->get( 'login.remember' ) );
	}

	public function logout( $redirectController = NULL, $redirectAction = NULL ){
		$words		= (object) $this->getWords( 'logout' );

		if( $this->session->has( 'userId' ) ){
			$this->env->getCaptain()->callHook( 'Auth', 'onBeforeLogout', $this, array(
				'userId'	=> $this->session->get( 'userId' ),
				'roleId'	=> $this->session->get( 'roleId' ),
			) );
			$this->session->remove( 'userId' );
			$this->session->remove( 'roleId' );
			$this->session->remove( 'token' );
			if( $this->request->has( 'autoLogout' ) ){
				$this->env->getMessenger()->noteNotice( $words->msgAutoLogout );
			}
			else{
//				$this->cookie->remove( 'auth_remember' );
//				$this->cookie->remove( 'auth_remember_id' );
//				$this->cookie->remove( 'auth_remember_pw' );
				$this->env->getMessenger()->noteSuccess( $words->msgSuccess );
			}
			if( $this->moduleConfig->get( 'logout.clearSession' ) )									//  session is to be cleared on logout
				session_destroy();																	//  completely destroy session
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

	public function register(){
		$data	= array();
		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAllFromSource( 'POST' );
			$result	= $this->logic->register( $data );
			if( is_array( $result ) ){
				$this->messenger->noteSuccess( 'Account has been created. Now, please confirm you account!' );
				$from	= $this->request->get( 'from' );
				if( $from ){
//					if( preg_match( '/:\/\//', $from ) )
//						$this->relocate( $from );
					$from->restart( $from );
				}
				$this->session->set( 'registered_account_id', $result['accountId'] );
				$this->restart( 'auth/rest/login' );
			}
			if( !preg_match( '/^[a-z]+:-[0-9]+/$i', $result ) )
				throw new InvalidArgumentException( 'Invalid reponse code' );
			list( $level, $code )	= explode( ':', $result, 2 );
			$message	= 'error-'.$level.$code;
			$message	= isset( $words[$message] ) ? $words[$message] : $message;
			$this->messenger->noteError( $message );
			$data		= array_merge( array(
				'business'			=> FALSE,
				'billing_address'	=> FALSE,
			), $data );
		}
		if( !$data ){
			$data	= array(
				'firstname'			=> 'Hans',
				'surname'			=> 'Testmann',
				'username'			=> 'htestmann',
				'email'				=> 'hans.testmann@aol.com',
				'phone'				=> '0123/4567890',
				'country'			=> 'Deutschland',
				'state'				=> '',
				'postcode'			=> '12345',
				'city'				=> 'Stadt 1',
				'street'			=> 'Straße 1',
				'business'			=> 'on',
				'company'			=> 'Testmann Inc.',
				'tax_id'			=> '123-456-789',
				'billing_address'	=> 'on',
				'billing_country'	=> 'Deutschland',
				'billing_state'		=> '',
				'billing_postcode'	=> '98765',
				'billing_city'		=> 'Stadt 2',
				'billing_street'	=> 'Straße 2',
				'billing_email'		=> 'testmann@aol.com',
				'billing_phone'		=> '0123/9876543',
			);
		}

		$this->addData( 'data', (object) $data );
		$this->addData( 'from', $this->request->get( 'from' ) );									//  forward redirect URL to form action
	}

	public function registered(){
		$lastRegistration	= $this->session->get( 'registered_account_id' );
		return "YESSSSS!";
	}

	/**
	 *	@todo    	rewrite this method! local use of model is not possible a the JSON server has no method to compare password hashes, yet.
	 *	This method is deactivated because the currently only available JSON server auth controller (@App_Chat_Server) does not support relogin.
	 */
/*	protected function rememberUserInCookie( $userId, $password ){
		$expires	= strtotime( "+2 years" ) - time();
		$passwordHash	= md5( sha1( $password ) );													//  hash password using SHA1 and MD5
		if( version_compare( PHP_VERSION, '5.5.0' ) >= 0 )											//  for PHP 5.5.0+
			$passwordHash	= password_hash( $password, PASSWORD_BCRYPT );							//  hash password using BCRYPT
		$this->cookie->set( 'auth_remember', TRUE, $expires );
		$this->cookie->set( 'auth_remember_id', $userId, $expires );
		$this->cookie->set( 'auth_remember_pw', $passwordHash, $expires );
	}*/

	/**
	 *	@todo    	rewrite this method! local use of model is not possible a the JSON server has no method to compare password hashes, yet.
	 *	Tries to relogin user if remembered in cookie.
	 *	Retrieves user ID and password from cookie.
	 *	Checks user, its password and access per role.
	 *	Stores user ID and role ID in session on success.
	 *	Redirects to "from" if given.
	 *	@access		public
	 *	@return		void
	 */
/*	protected function tryLoginByCookie(){
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
	}*/
}
?>
