<?php

use CeusMedia\Common\Net\HTTP\Cookie as HttpCookie;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Auth_Json extends Controller
{
	protected $config;
	protected $request;
	protected $session;
	protected $cookie;
	protected $messenger;
	protected $useCsrf;

	public function index()
	{
		if( !$this->logic->isAuthenticated() )
			return $this->redirect( 'auth/json', 'login' );										// @todo replace redirect

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

	public function login( $username = NULL )
	{
		if( $this->logic->isAuthenticated() ){
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
			if( !trim( $password = $this->request->get( 'login_password' ) ) )
				$this->messenger->noteError( $words->msgNoPassword );

			$data	= array(
				'filters'	=> array(
					'username'	=> $username,
//					'password'	=> md5( $password )
				)
			);
			$result	= $this->env->getServer()->postData( 'user', 'index', NULL, $data );
			$user	= count( $result ) === 1 ? $result[0] : NULL;

			if( !$this->messenger->gotError() ){
				if( !$user )
					$this->messenger->noteError( $words->msgInvalidUser );
				else{
					$payload	= [
						'backend'	=> 'json',
						'username'	=> $user ? $user->username : $username,
		//				'password'	=> $password,															//  disabled for security
						'userId'	=> $user ? $user->userId : 0,
					];
					$result	= $this->callHook( 'Auth', 'checkBeforeLogin', $this, $payload );
					$role	= $this->env->getServer()->postData( 'role', 'get', [$user->roleId] );
					if( !$role->access )
						$this->messenger->noteError( $words->msgInvalidRole );
					else if( $user->status == 0 )
						$this->messenger->noteError( $words->msgUserUnconfirmed );
					else if( $user->status == -1 )
						$this->messenger->noteError( $words->msgUserLocked );
					else if( $user->status == -2 )
						$this->messenger->noteError( $words->msgUserDisabled );
					else if( !$this->checkPasswordOnLogin( $user, $password ) )						//  validate password
						$this->messenger->noteError( $words->msgInvalidPassword );
					if( !$this->messenger->gotError() ){
						$this->messenger->noteSuccess( $words->msgSuccess );
						$this->session->set( 'auth_user_id', $user->userId );
						$this->session->set( 'auth_role_id', $user->roleId );
						$this->session->set( 'auth_backend', 'Json' );
						$this->logic->setAuthenticatedUser( $user );
//						if( $this->request->get( 'login_remember' ) )
//							$this->rememberUserInCookie( $user->userId, $password );
						$from	= $this->request->get( 'from' );									//  get redirect URL from request if set
						$from	= !preg_match( "/auth\/logout/", $from ) ? $from : '';				//  exclude logout from redirect request
						$this->restart( './auth/json?from='.$from );												//  restart (or go to redirect URL)
					}
				}
			}
		}
//		$this->cookie->remove( 'auth_remember' );
		$this->addData( 'from', $this->request->get( 'from' ) );									//  forward redirect URL to form action
		$this->addData( 'login_username', $username );
//		$this->addData( 'login_remember', (boolean) $this->cookie->get( 'auth_remember' ) );
		$this->addData( 'useRegister', $this->moduleConfig->get( 'register' ) );
//		$this->addData( 'useRemember', $this->moduleConfig->get( 'login.remember' ) );
	}

	public function logout( $redirectController = NULL, $redirectAction = NULL )
	{
		$words		= (object) $this->getWords( 'logout' );

		if( $this->logic->isAuthenticated() ){
			$payload	= [
				'userId'	=> $this->session->get( 'auth_user_id' ),
				'roleId'	=> $this->session->get( 'auth_role_id' ),
			];
			$this->env->getCaptain()->callHook( 'Auth', 'onBeforeLogout', $this, $payload );
			$this->logic->clearCurrentUser();
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

	protected function __onInit(): void
	{
		$this->config		= $this->env->getConfig();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
//		$this->cookie		= new HttpPartitionCookie( "hydrogen", "/" );
		$this->cookie		= new HttpCookie( parse_url( $this->env->url, PHP_URL_PATH ) );
		if( isset( $this->env->version ) )
			if( version_compare( $this->env->version, '0.8.6.5', '>=' ) )
				$this->cookie	= $this->env->getCookie();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= $this->env->getLogic()->get( 'Authentication_Backend_Json' );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_authentication_backend_json.', TRUE );
		$this->addData( 'useCsrf', $this->useCsrf = $this->env->getModules()->has( 'Security_CSRF' ) );
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
	protected function checkPasswordOnLogin( string $user, string $password ): bool
	{
		$data	= array(
			'filters'	=> array(
				'userId'	=> $user->userId,
				'password'	=> md5( $password )
			)
		);
		$result	= $this->env->getServer()->postData( 'user', 'index', NULL, $data );
		return count( $result ) === 1;
	}

	/**
	 *	@todo    	rewrite this method! local use of model is not possible a the JSON server has no method to compare password hashes, yet.
	 *	This method is deactivated because the currently only available JSON server auth controller (@App_Chat_Server) does not support relogin.
	 */
/*	protected function rememberUserInCookie( $userId, $password ){
		$expires	= strtotime( "+2 years" ) - time();
		$passwordHash	= md5( sha1( $password ) );													//  hash password using SHA1 and MD5
		if( $this->env->getPhp()->version->isAtLeast( '5.5.0' ) )											//  for PHP 5.5.0+
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
					if( $this->env->getPhp()->version->isAtLeast( '5.5.0' ) )						//  for PHP 5.5.0+
						$passwordMatch	= password_verify( $user->password, $password );			//  verify password hash
					if( $passwordMatch ){															//  password from cookie is matching
						$modelUser->edit( $user->userId, array( 'loggedAt' => time() ) );			//  note login time in database
						$this->session->set( 'auth_user_id', $user->userId );						//  set user ID in session
						$this->session->set( 'auth_role_id', $user->roleId );						//  set user role in session
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
