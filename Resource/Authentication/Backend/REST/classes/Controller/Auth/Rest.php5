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

	public function ajaxUsernameExists(){
		$username	= trim( $this->request->get( 'username' ) );
		$result		= FALSE;
		if( strlen( $username ) ){
			$data		= array ( 'filters' => array( 'username' => $username ) );
			$result		= $this->env->getServer()->postData( 'user', 'index', NULL, $data );
			$result		= count( $result ) === 1;
		}
		print( json_encode( $result ) );
		exit;
	}

	public function ajaxEmailExists(){
		$email	= trim( $this->request->get( 'email' ) );
		$result		= FALSE;
		if( strlen( $email ) ){
			$data		= array ( 'filters' => array( 'email' => $email ) );
			$result		= $this->env->getServer()->postData( 'user', 'index', NULL, $data );
			$result		= count( $result ) === 1;
		}
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
				if( $user->data == -9 )
					$this->messenger->noteError( $words->msgInvalidDomain );
				else if( $user->data == -10 )
					$this->messenger->noteError( $words->msgInvalidUser );
				else if( $user->data == -11 )
					$this->messenger->noteError( $words->msgInvalidPassword );
				else if( $user->data == -12 )
					$this->messenger->noteError( $words->msgUserDisabled );
				else if( $user->data == -13 )
					$this->messenger->noteError( $words->msgDomainDisabled );
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
