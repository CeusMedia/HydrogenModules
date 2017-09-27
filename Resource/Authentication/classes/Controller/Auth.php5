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
		if( $userId ){
			$cookie		= new Net_HTTP_Cookie( parse_url( $env->url, PHP_URL_PATH ) );
			$remember	= (bool) $cookie->get( 'auth_remember' );
			$env->getSession()->set( 'isRemembered', $remember );
			$script		= 'Auth.init('.$userId.','.json_encode( $remember ).');';											//  initialize Auth class with user ID
			$env->getPage()->js->addScriptOnReady( $script, 1 );															//  enlist script to be run on ready
		}
	}

	public function ajaxIsAuthenticated(){
		print( json_encode( $this->session->has( 'userId' ) ) );
		exit;
	}

	public function ajaxRefreshSession(){
		$this->ajaxIsAuthenticated();
	}

	protected function redirectAfterLogin(){
		$moduleConfig	= $this->config->getAll( 'module.resource_authentication.', TRUE );
		$from			= str_replace( "index/index", "", $this->request->get( 'from' ) );
		$forwardPath	= $moduleConfig->get( 'login.forward.path' );
		$forwardForce	= $moduleConfig->get( 'login.forward.force' );

		if( $forwardPath && $forwardForce )
			$this->restart( $forwardPath.( $from ? '?from='.$from : '' ) );
		if( $from )
			$this->restart( $from );
		if( $forwardPath )
			$this->restart( $forwardPath.( $from ? '?from='.$from : '' ) );
		$this->restart( NULL );
	}

	protected function redirectAfterLogout(){
		$moduleConfig	= $this->config->getAll( 'module.resource_authentication.', TRUE );
		$from			= $this->request->get( 'from' );
		$forwardPath	= $moduleConfig->get( 'logout.forward.path' );
		$forwardForce	= $moduleConfig->get( 'logout.forward.force' );

		if( $forwardPath && $forwardForce )
			$this->restart( $forwardPath.( $from ? '?from='.$from : '' ) );
		if( $from )
			$this->restart( $from );
		if( $forwardPath )
			$this->restart( $forwardPath.( $from ? '?from='.$from : '' ) );
		$this->restart( NULL );

	}

	public function index(){
		if( !$this->session->has( 'userId' ) )
			return $this->redirect( 'auth', 'login' );
		$this->redirectAfterLogin();
	}


	protected function getBackend(){
		$backends		= $this->logic->getBackends();
		$backendKey		= $this->session->get( 'authBackend' );
		$backendKeys	= array_keys( $backends );
		if( !$backends )
			throw new RuntimeException( 'No authentication backend available' );
		if( !$backendKey || in_array( $backendKey, $backendKeys ) )
			$backendKey	= $backendKeys[0];
		return $backends[$backendKey];
	}

	public function login( $username = NULL ){
		if( $this->session->has( 'userId' ) )
			$this->redirectAfterLogin();
		$backend	= $this->getBackend();
		$path		= 'auth/'.$backend->path.'/login';
		if( $username )
			$path	.= '/'.$username;
		$from		= $this->request->get( 'from' );
		$from		= str_replace( "index/index", "", $from );
		$this->restart( $from ? $path.'?from='.$from : $path );
	}

	public function logout(){
		$backend	= $this->getBackend();
		$path		= 'auth/'.strtolower( $backend ->path ).'/logout';
		$from		= $this->request->get( 'from' );
		$from		= str_replace( "index/index", "", $from );
		$this->restart( $from ? $path.'?from='.$from : $path );
	}

	public function password(){
		$backend	= $this->getBackend();
		$path		= 'auth/'.strtolower( $backend->path ).'/password';
		$from		= $this->request->get( 'from' );
		$from		= str_replace( "index/index", "", $from );
		$this->restart( $from ? $path.'?from='.$from : $path );
	}

	public function register(){
		$backend	= $this->getBackend();
		$path		= 'auth/'.strtolower( $backend->path ).'/register';
		$from		= $this->request->get( 'from' );
		$from		= str_replace( "index/index", "", $from );
		$this->restart( $from ? $path.'?from='.$from : $path );
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
