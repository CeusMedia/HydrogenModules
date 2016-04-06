<?php
class Controller_Auth extends CMF_Hydrogen_Controller {

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
		$this->logic		= Logic_Authentication::getInstance( $this->env );
//		$this->cookie		= new Net_HTTP_PartitionCookie( "hydrogen", "/" );
		$this->cookie		= new Net_HTTP_Cookie( parse_url( $this->env->url, PHP_URL_PATH ) );
		$this->messenger	= $this->env->getMessenger();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_authentication.', TRUE );
		$this->addData( 'useCsrf', $this->useCsrf = $this->env->getModules()->has( 'Security_CSRF' ) );
	}

	static public function ___onPageApplyModules( CMF_Hydrogen_Environment_Abstract $env, $context, $module, $data = array() ){
		$userId		= (int) $env->getSession()->get( 'userId' );														//  get ID of current user (or zero)
		$cookie		= new Net_HTTP_Cookie( parse_url( $env->url, PHP_URL_PATH ) );
		$remember	= (bool) $cookie->get( 'auth_remember' );
		$env->getSession()->set( 'isRemembered', $remember );
		$script		= 'Auth.init('.$userId.','.json_encode( $remember ).');';											//  initialize Auth class with user ID
		$env->getPage()->js->addScriptOnReady( $script, 1 );															//  enlist script to be run on ready
	}

	public function ajaxIsAuthenticated(){
		print( json_encode( $this->session->has( 'userId' ) ) );
		exit;
	}

	public function ajaxRefreshSession(){
		$this->ajaxIsAuthenticated();
	}

	public function ajaxUsernameExists(){
		$username	= trim( $this->request->get( 'username' ) );
		$result		= FALSE;
		if( strlen( $username ) ){
			$modelUser		= new Model_User( $this->env );
			$result			= (bool) $modelUser->countByIndex( 'username', $username );
		}
		print( json_encode( $result ) );
		exit;
	}

	public function ajaxEmailExists(){
		$email	= trim( $this->request->get( 'email' ) );
		$result		= FALSE;
		if( strlen( $email ) ){
			$modelUser		= new Model_User( $this->env );
			$result			= (bool) $modelUser->countByIndex( 'email', $email );
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

	public function confirm( $code = NULL ){
		$words		= (object) $this->getWords( 'confirm' );
		$code		= $code ? $code : $this->request->get( 'confirm_code' );											//  get code from POST reqeuest if not given by GET
		$from		= $this->request->get( 'from'  );
		$from		= str_replace( "index/index", "", $from );

		if( strlen( trim( (string) $code ) ) ){
			$passwordSalt	= trim( $this->config->get( 'module.resource.users.password.salt' ) );						//  string to salt password with
			$modelUser		= new Model_User( $this->env );
			$users			= $modelUser->getAllByIndex( 'status', 0 );
			foreach( $users as $user ){
				$pak	= md5( 'pak:'.$user->userId.'/'.$user->username.'&'.$passwordSalt );
				if( $pak === $code ){
					$modelUser->edit( $user->userId, array( 'status' => 1 ) );
					$this->messenger->noteSuccess( $words->msgSuccess );
					$result	= $this->callHook( 'Auth', 'afterConfirm', $this, array( 'userId' => $user->userId ) );
					$this->restart( './auth/login/'.$user->username.( $from ? '?from='.$from : '' ) );
				}
			}
			$this->messenger->noteError( $words->msgInvalidCode );
		}
		$this->addData( 'pak', $code );
		$this->addData( 'from', $from );									//  forward redirect URL to form action
	}

	public function index(){
		if( !$this->session->has( 'userId' ) )
			return $this->redirect( 'auth', 'login' );

		$from			= str_replace( "index/index", "", $this->request->get( 'from' ) );
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
		$backends	= $this->logic->getBackends();
		if( count( $backends ) === 1 ){
			$path	= 'auth/'.strtolower( $backends[0] ).'/login';
			if( $username )
				$path	.= '/'.$username;
			$from		= $this->request->get( 'from' );
			$from		= str_replace( "index/index", "", $from );
			$path		= $from ? $path.'?from='.$from : $path;
			$this->restart( $path );
		}

		$this->addData( 'backends', $backends );
/*

		if( $this->session->has( 'userId' ) ){
			if( $this->request->has( 'from' ) )
				$this->restart( $from );
			return $this->redirect( 'auth', 'loginInside' );
		}

		$this->tryLoginByCookie();
		$words		= (object) $this->getWords( 'login' );

		if( $this->request->has( 'doLogin' ) ) {

			if( $this->useCsrf ){
				$controller	= new Controller_Csrf( $this->env );
				$controller->checkToken();
			}
			if( !trim( $username = $this->request->get( 'login_username' ) ) )
				$this->messenger->noteError( $words->msgNoUsername );
			if( !trim( $password = $this->request->get( 'login_password' ) ) )
				$this->messenger->noteError( $words->msgNoPassword );

			$modelUser	= new Model_User( $this->env );
			$modelRole	= new Model_Role( $this->env );
			$user		= $modelUser->getByIndex( 'username', $username );

			$result	= $this->callHook( 'Auth', 'checkBeforeLogin', $this, $data = array(
				'username'	=> $username,
				'password'	=> $password,
				'userId'	=> $user ? $user->userId : 0,
			) );

			if( !$this->messenger->gotError() ){
				if( !$user )
					$this->messenger->noteError( $words->msgInvalidUser );
				else{
					$role	= $modelRole->get( $user->roleId );
					if( !$role->access )
						$this->messenger->noteError( $words->msgInvalidRole );
					else if( $user->password !== md5( $password ) )
						$this->messenger->noteError( $words->msgInvalidPassword );
					else if( $user->status == 0 )
						$this->messenger->noteError( $words->msgUserUnconfirmed );
					else if( $user->status == -1 )
						$this->messenger->noteError( $words->msgUserLocked );
					else if( $user->status == -2 )
						$this->messenger->noteError( $words->msgUserDisabled );

					if( !$this->messenger->gotError() ){
						$modelUser->edit( $user->userId, array( 'loggedAt' => time() ) );
						$this->messenger->noteSuccess( $words->msgSuccess );
						$this->session->set( 'userId', $user->userId );
						$this->session->set( 'roleId', $user->roleId );
						if( $this->request->get( 'login_remember' ) )
							$this->rememberUserInCookie( $user );
						$from	= str_replace( "index/index", "", $this->request->get( 'from' ) );	//  get redirect URL from request if set
						$from	= !preg_match( "/auth\/logout/", $from ) ? $from : '';				//  exclude logout from redirect request
						$this->restart( './auth?from='.$from );										//  restart (or go to redirect URL)
					}
				}
			}
		}
//		$this->cookie->remove( 'auth_remember' );
		$this->addData( 'from', $this->request->get( 'from' ) );									//  forward redirect URL to form action
		$this->addData( 'login_username', $username );
		$this->addData( 'login_remember', (boolean) $this->cookie->get( 'auth_remember' ) );
		$this->addData( 'useRegister', $this->moduleConfig->get( 'register' ) );
		$this->addData( 'useRemember', $this->moduleConfig->get( 'login.remember' ) );
*/
	}

	public function logout(){
		$backends	= $this->logic->getBackends();
		$backend	= $this->session->get( 'authBackend' );
		$path		= 'auth/'.strtolower( $backend ? $backend : $backends[0] ).'/logout';
		$from		= str_replace( "index/index", "", $this->request->get( 'from' ) );
		$path		= $from ? $path.'?from='.$from : $path;
		$this->restart( $path );
	}

	public function password(){
		$backends	= $this->logic->getBackends();
		$backend	= $this->session->get( 'authBackend' );
		$path		= 'auth/'.strtolower( $backend ? $backend : $backends[0] ).'/password';
		$from		= str_replace( "index/index", "", $this->request->get( 'from' ) );
		$path		= $from ? $path.'?from='.$from : $path;
		$this->restart( $path );
	}

	public function register(){
		$backends	= $this->logic->getBackends();
		$backend	= $this->session->get( 'authBackend' );
		$path		= 'auth/'.strtolower( $backend ? $backend : $backends[0] ).'/register';
		$from		= str_replace( "index/index", "", $this->request->get( 'from' ) );
		$path		= $from ? $path.'?from='.$from : $path;
		$this->restart( $path );
	}

	protected function rememberUserInCookie( $user ){
		$expires	= strtotime( "+2 years" ) - time();
		$passwordHash	= md5( sha1( $user->password ) );											//  hash password using SHA1 and MD5
		if( version_compare( PHP_VERSION, '5.5.0' ) >= 0 )											//  for PHP 5.5.0+
			$passwordHash	= password_hash( $user->password, PASSWORD_BCRYPT );					//  hash password using BCRYPT
		$this->cookie->set( 'auth_remember', TRUE, $expires );
		$this->cookie->set( 'auth_remember_id', $user->userId, $expires );
		$this->cookie->set( 'auth_remember_pw', $passwordHash, $expires );
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
						$from	= str_replace( "index/index", "", $this->request->get( 'from' ) );	//  get redirect URL from request if set
						$from	= !preg_match( "/auth\/logout/", $from ) ? $from : '';				//  exclude logout from redirect request
						$this->restart( './'.$from );												//  restart (or go to redirect URL)
					}
				}
			}
		}
	}
}
?>
