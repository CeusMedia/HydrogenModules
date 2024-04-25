<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Cookie as HttpCookie;
use CeusMedia\Common\Net\HTTP\Request;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger;
use JetBrains\PhpStorm\NoReturn;

class Controller_Auth extends Controller
{
	public static string $moduleId	= 'Resource_Authentication';

	protected Dictionary $config;
	protected Request $request;
	protected Dictionary $session;
	protected HttpCookie $cookie;
	protected Messenger $messenger;
	protected bool $useCsrf;
	protected Logic_Authentication $logic;

	/**
	 *	@deprecated		use Ajax::isAuthenticated instead
	 */
	public function ajaxIsAuthenticated(): void
	{
		print( json_encode( $this->session->has( 'auth_user_id' ) ) );
		exit;
	}

	/**
	 *	@deprecated		use Ajax::refreshSession instead
	 */
	public function ajaxRefreshSession(): void
	{
		$this->ajaxIsAuthenticated();
	}

	public function confirm(): void
	{
		$this->forwardToBackendAction( 'confirm' );
	}

	public function index( $arg1 = NULL, $arg2 = NULL ): void
	{
		if( !$this->logic->isAuthenticated() )
			$this->restart( 'login', TRUE );
		$this->redirectAfterLogin();
	}

	public function login( ?string $username = NULL ): void
	{
		if( $this->logic->isAuthenticated() )
			$this->redirectAfterLogin();
		$action		= 'login';
		if( $username )
			$action	.= '/'.$username;
		$this->forwardToBackendAction( $action );
	}

	public function logout(): void
	{
		if( !$this->logic->isAuthenticated() )
			$this->redirectAfterLogout();
		$this->forwardToBackendAction( 'logout' );
	}

	public function password(): void
	{
		$this->forwardToBackendAction( 'password' );
	}

	public function register(): void
	{
		$this->forwardToBackendAction( 'register' );
	}

	//  --  PROTECTED  --  //

	protected function __onInit(): void
	{
		$this->config		= $this->env->getConfig();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logic		= $this->env->getLogic()->get( 'Authentication' );
		$this->cookie		= new HttpCookie( parse_url( $this->env->url, PHP_URL_PATH ) );
		if( isset( $this->env->version ) )
			if( version_compare( $this->env->version, '0.8.6.5', '>=' ) )
				$this->cookie	= $this->env->getCookie();
		$this->messenger	= $this->env->getMessenger();
//		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_authentication.', TRUE );
		$this->addData( 'useCsrf', $this->useCsrf = $this->env->getModules()->has( 'Security_CSRF' ) );
	}

	protected function forwardToBackendAction( $action, $carryFrom = TRUE ): void
	{
		$backend	= $this->getBackend();
		$path		= 'auth/'.strtolower( $backend->path ).'/'.$action;
		$from		= trim( $this->request->get( 'from' ) );
		$from		= strlen( $from ) ? preg_replace( '|^index/?(index)?$|', '', $from ) : '';
		$path		= strlen( $from ) ? $path.'?from='.$from : $path;
		$this->restart( $path );
	}

	protected function getBackend()
	{
		$backends		= $this->logic->getBackends();
		$backendKey		= $this->session->get( 'auth_backend' );
		$backendKeys	= array_keys( $backends );
		if( !$backends )
			throw new RuntimeException( 'No authentication backend available' );
		if( !$backendKey || !array_key_exists( $backendKey, $backends ) )
			$backendKey	= $backendKeys[0];
		return $backends[$backendKey];
	}

	protected function redirectAfterLogin(): void
	{
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
		$this->restart();
	}

	protected function redirectAfterLogout(): void
	{
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
		$this->restart();
	}

	protected function rememberUserInCookie( $user ): void
	{
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
	protected function tryLoginByCookie(): void
	{
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
						$this->session->set( 'auth_user_id', $user->userId );						//  set user ID in session
						$this->session->set( 'auth_role_id', $user->roleId );						//  set user role in session
						$from	= str_replace( "index/index", "", $this->request->get( 'from' ) );	//  get redirect URL from request if set
						$from	= !preg_match( "/auth\/logout/", $from ) ? $from : '';				//  exclude logout from redirect request
						$this->restart( './'.$from );												//  restart (or go to redirect URL)
					}
				}
			}
		}
	}
}
