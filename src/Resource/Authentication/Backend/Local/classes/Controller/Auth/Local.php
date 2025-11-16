<?php /** @noinspection PhpNoReturnAttributeCanBeAddedInspection */

/** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Crypt\PasswordStrength;
use CeusMedia\Common\Alg\Randomizer;
use CeusMedia\Common\Alg\Validation\Predicates;
use CeusMedia\Common\Net\HTTP\Cookie as HttpCookie;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\LibraryInterface as ModuleLibraryInterface;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;

class Controller_Auth_Local extends Controller
{
	public static string $moduleId	= 'Resource_Authentication_Backend_Local';

	protected Dictionary $config;
	protected HttpRequest $request;
	protected Dictionary $session;
	protected HttpCookie $cookie;
	protected ?MessengerResource $messenger;
	protected ModuleLibraryInterface $modules;
	protected bool $useCsrf;
	protected bool $useOauth2;
	protected Dictionary $moduleConfigAuth;
	protected Dictionary $moduleConfigUsers;
	protected ?Logic_Limiter $limiter			= NULL;
	protected Logic_Authentication_Backend_Local $logic;

	/**
	 *	@throws		ReflectionException
	 *	@todo		send mail to user after confirmation with user data
	 */
	public function confirm( ?string $code = NULL ): void
	{
		$words		= (object) $this->getWords( 'confirm' );
		$code		= $code ?: $this->request->get( 'confirm_code' );												//  get code from POST reqeuest if not given by GET
		$from		= $this->request->get( 'from'  );
		$from		= str_replace( "index/index", "", $from );

		if( '' !== trim( (string) $code ) ){
			$passwordSalt	= trim( $this->config->get( 'module.resource_users.password.salt', '' ) );						//  string to salt password with
			$modelUser		= new Model_User( $this->env );
			$users			= $modelUser->getAllByIndex( 'status', 0 );
			foreach( $users as $user ){
				$pak	= md5( 'pak:'.$user->userId.'/'.$user->username.'&'.$passwordSalt );
				if( $pak === $code ){
					$modelUser->edit( $user->userId, ['status' => 1] );
					$this->messenger->noteSuccess( $words->msgSuccess );
					$payload	= [
						'userId'	=> $user->userId,
						'roleId'	=> $user->roleId,
						'from'		=> $from,
					];
					$this->callHook( 'Auth', 'afterConfirm', $this, $payload );
					if( 1 ){
						$this->messenger->noteSuccess( $words->msgSuccessAutoLogin );
						$this->session->set( Logic_Authentication::$sessionKeyAuthUserId, $user->userId );
						$this->session->set( Logic_Authentication::$sessionKeyAuthRoleId, $user->roleId );
						if( $from )
							$this->restart( $from );
					}
					$this->restart( './auth/local/login?login_username='.$user->username.( $from ? '&from='.$from : '' ) );
				}
			}
			$this->messenger->noteError( $words->msgInvalidCode );
		}
		$this->addData( 'pak', $code );
		$this->addData( 'from', $from );									//  forward redirect URL to form action
	}

	public function index(): void
	{
		if( !$this->session->has( Logic_Authentication::$sessionKeyAuthUserId ) )
			$this->restart( 'auth/login' );

		$from			= $this->request->get( 'from' );
		$forwardPath	= $this->moduleConfig->get( 'login.forward.path' );
		$forwardForce	= $this->moduleConfig->get( 'login.forward.force' );

		if( $forwardPath && $forwardForce )
			$this->restart( $forwardPath.( $from ? '?from='.$from : '' ) );
		if( $from )
			$this->restart( $from );
		if( $forwardPath )
			$this->restart( $forwardPath.( $from ? '?from='.$from : '' ) );
		$this->restart();
	}

	/**
	 *	@throws		ReflectionException
	 *	@throws		SimpleCacheInvalidArgumentException
	 *	@todo		implement username parameter to be used (not the case right now)
	 */
	public function login( $username = NULL ): void
	{
		if( $this->session->has( Logic_Authentication::$sessionKeyAuthUserId ) ){
			$this->redirectAfterLoginIfPasswordUpdateNeeded( $this->session->get( Logic_Authentication::$sessionKeyAuthUserId ) );
			$this->redirectAfterLogin();
		}

		$this->session->set( Logic_Authentication::$sessionKeyAuthBackend, 'Local' );

//		$this->tryLoginByCookie();		// disabled: insecure and not supporting user password resource

		$this->tryLoginByPostRequest();

		$from		= $this->request->get( 'from' );

//		$this->cookie->remove( 'auth_remember' );
		$this->addData( 'from', $from );													//  forward redirect URL to form action
		$this->addData( 'login_username', $username );
		$this->addData( 'login_remember', (boolean) $this->cookie->get( 'auth_remember' ) );

		$useRegisterByConfig	= $this->moduleConfig->get( 'register' ) && $this->moduleConfigAuth->get( 'register' );
		$useRegisterByLimit		= !$this->limiter || !$this->limiter->denies( 'Auth.Local.Login:register' );

		$this->addData( 'useRegister', $useRegisterByConfig && $useRegisterByLimit );

		$useRememberByConfig	= $this->moduleConfig->get( 'login.remember' );
		$useRememberByLimit		= !$this->limiter || !$this->limiter->denies( 'Auth.Local.Login:remember' );
		$this->addData( 'useRemember', $useRememberByConfig && $useRememberByLimit );
	}

	/**
	 *	@param		?string		$redirectController
	 *	@param		?string		$redirectAction
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function logout( ?string $redirectController = NULL, ?string $redirectAction = NULL ): void
	{
		$words		= (object) $this->getWords( 'logout' );
		$logicAuth	= $this->env->getLogic()->get( 'Authentication' );
		if( $this->session->has( Logic_Authentication::$sessionKeyAuthUserId ) ){
			$payload	= [
				'userId'	=> $this->session->get( Logic_Authentication::$sessionKeyAuthUserId ),
				'roleId'	=> $this->session->get( Logic_Authentication::$sessionKeyAuthRoleId ),
			];
			$this->env->getCaptain()->callHook( 'Auth', 'onBeforeLogout', $this, $payload );
			$this->session->remove( Logic_Authentication::$sessionKeyAuthUserId );
			$this->session->remove( Logic_Authentication::$sessionKeyAuthRoleId );
			$logicAuth->clearCurrentUser();
			if( $this->request->has( 'autoLogout' ) ){
				$this->env->getMessenger()->noteNotice( $words->msgAutoLogout );
			}
			else{
				$this->cookie->remove( 'auth_remember' );
				$this->cookie->remove( 'auth_remember_id' );
				$this->cookie->remove( 'auth_remember_pw' );
				$this->env->getMessenger()->noteSuccess( $words->msgSuccess );
			}
			if( $this->moduleConfig->get( 'logout.clearSession' ) )									//  session is to be cleared on logout
				session_destroy();																	//  completely destroy session
		}
		$this->redirectAfterLogout( $redirectController, $redirectAction );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function password(): void
	{
		$words			= (object) $this->getWords( 'password' );
		$modelUser		= new Model_User( $this->env );

		$options		= $this->moduleConfigUsers;
		$passwordPepper	= trim( $options->get( 'password.pepper' ) );								//  string to pepper password with

		if( $this->request->has( 'sendPassword' ) ){
			if( !( $email = $this->request->get( 'password_email' ) ) ){
				$this->messenger->noteError( $words->msgNoEmail );
				$this->restart( 'password', TRUE );
			}
			/** @var Entity_User $user */
			$user	= $modelUser->getByIndex( 'email', $email );
			if( NULL === $user ){
				$this->messenger->noteError( $words->msgInvalidEmail );
			}
			else{
				$randomizer	= new Randomizer();
				$randomizer->configure( TRUE, TRUE, TRUE, FALSE, 0 );
				$password	= $randomizer->get( 8 );

				$this->env->getDatabase()->beginTransaction();
				try{
					$data		= [
						'firstname'	=> $user->firstname,
						'surname'	=> $user->surname,
						'username'	=> $user->username,
						'password'	=> $password,
					];
					$language	= $this->env->getLanguage()->getLanguage();
					$mail		= new Mail_Auth_Local_Password( $this->env, $data );
					$logic		= Logic_Mail::getInstance( $this->env );
					$logic->appendRegisteredAttachments( $mail, $language );
					$logic->sendQueuedMail( $logic->enqueueMail( $mail, $language, $user ) );
					if( class_exists( 'Logic_UserPassword' ) ){										//  @todo  remove line if old user password support decays
						$logic	= Logic_UserPassword::getInstance( $this->env );
						$logic->addPassword( $user, $password );
					}
					else{																			//  @todo  remove whole block if old user password support decays
						$crypt		= md5( $password.$passwordPepper );
						$modelUser->edit( $user->userId, ['password' => $crypt] );

					}
					$this->env->getDatabase()->commit();
					$this->messenger->noteSuccess( $words->msgSuccess );
	//				$this->messenger->noteNotice( 'Neues Passwort: '.$password." <small><em>(Diese Meldung kommt nicht im Live-Betrieb.)</em></small>" );	//  @todo: remove before going live
					$this->restart( './auth/local/login?login_username='.$user->username );
				}
				catch( Exception $e ){
					$this->messenger->noteFailure( $words->msgSendingMailFailed );
					$payload	= ['exception' => $e];
					$this->callHook( 'Env', 'logException', $this, $payload );
				}
				$this->env->getDatabase()->rollBack();
			}
		}
		$this->addData( 'password_email', $this->request->get( 'password_email' ) );
	}

	/**
	 *	@throws		ReflectionException
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function register(): void
	{
		$words		= (object) $this->getWords( 'register' );

		if( !$this->moduleConfigAuth->get( 'register' ) || !$this->moduleConfig->get( 'register' ) ){
			$this->messenger->noteError( $words->msgRegistrationClosed );
			$this->restart( $this->request->get( 'from' ) );
		}

		$this->registerByPostRequest();

		$input	= new Dictionary();
		if( $this->session->get( 'auth_register_oauth_user_id' ) ){
			$fields	= ['username', 'email', 'gender', 'firstname', 'surname', 'street', 'postcode', 'city', 'phone'];
			foreach( $fields as $field )
				if( !isset( $input[$field] ) || !strlen( trim( $input[$field] ) ) )
					$input[$field]	= $this->session->get( 'auth_register_oauth_'.$field );
		}
		foreach( $input as $key => $value )
			$input[$key]	= htmlentities( $value, ENT_COMPAT, 'UTF-8' );
		if( !$input->get( 'country' ) && 'de' === $this->env->getLanguage()->getLanguage() )
			$input->set( 'country', 'DE' );

		$this->addData( 'user', $input );
		$this->addData( 'from', $this->request->get( 'from' ) );									//  forward redirect URL to form action
		$this->addData( 'countries', $this->env->getLanguage()->getWords( 'countries' ) );
	}

	/**
	 *	Displays form to set a new password for current user, since the current password needs to be updated.
	 *
	 *	@param		string|NULL		$hash		Passwort hash for handling GET confirm requests
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function update( ?string $hash = NULL ): void
	{
		$logicAuth		= Logic_Authentication::getInstance( $this->env );
		$logicPassword	= Logic_UserPassword::getInstance( $this->env );

		/** @var ?Entity_User $user */
		$user	= $logicAuth->getCurrentUser();
		if( NULL === $user || !$logicPassword->needsPasswordUpdate( $user ) )
			$this->restart();

		if( $this->request->getMethod()->isPost() )
			$this->handlePasswordUpdatePostRequest( $user );										//  handle POST request
		if( '' !== ( $hash ?? '' )	 )
			$this->handlePasswordUpdateConfirmGetRequest( $user, $hash );

		$this->addData( 'password', $this->request->get( 'password' ) );

		$from		= $this->request->get( 'from', '' );
		$from		= str_replace( 'index/index', '', $from );
		$this->addData( 'from', $from );														//  forward redirect URL to form action

	}


	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->config		= $this->env->getConfig();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->cookie		= new HttpCookie( parse_url( $this->env->url, PHP_URL_PATH ) );
		if( isset( $this->env->version ) )
			if( version_compare( $this->env->version, '0.8.6.5', '>=' ) )
				$this->cookie	= $this->env->getCookie();
		$this->messenger	= $this->env->getMessenger();
		$this->modules		= $this->env->getModules();
		$this->useCsrf		= $this->modules->get( 'Security_CSRF' )?->isActive ?? FALSE;
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logic		= $this->env->getLogic()->get( 'Authentication_Backend_Local' );

		$this->useOauth2	= FALSE;																//  assume that OAuth2 is not installed or registers as login tab
		if( $this->modules->has( 'Resource_Authentication_Backend_OAuth2' ) ){						//  OAuth2 is installed
			$module		= $this->modules->get( 'Resource_Authentication_Backend_OAuth2' );			//  get module object
			if( $module->isActive && isset( $module->config['loginMode'] ) )						//  module is enabled and login mode is defined
				$this->useOauth2	= $module->config['loginMode']->value === 'buttons';			//  use OAuth2 in local login only in buttons mode
		}

		$this->moduleConfigAuth		= $this->config->getAll( 'module.resource_authentication.', TRUE );
		$this->moduleConfigUsers	= $this->config->getAll( 'module.resource_users.', TRUE );
//		$this->moduleConfigAuth		= $this->env->getModules()->get( 'Resource_Authentication' )->getConfigAsDictionary();
//		$this->moduleConfigUsers	= $this->env->getModules()->get( 'Resource_Users' )->getConfigAsDictionary();
		if( $this->modules->has( 'Resource_Limiter' ) )
//			if( $this->modules->get( 'Resource_Limiter' )->isActive )				// @todo apply this line here and anywhere else
				$this->limiter	= Logic_Limiter::getInstance( $this->env );
		$this->addData( 'limiter', $this->limiter );
		$this->addData( 'useCsrf', $this->useCsrf );
		$this->addData( 'useOauth2', $this->useOauth2 );
	}

	/**
	 *	@param		string		$username
	 *	@param		string		$password
	 *	@return		?Entity_User
	 *	@throws		ReflectionException
	 */
	protected function authenticateUserByCredentials( string $username, string $password ): ?Entity_User
	{
		$words		= (object) $this->getWords( 'login' );
		if( !strlen( $username ) ){
			$this->messenger->noteError( $words->msgNoUsername );
			return NULL;
		}
		if( !trim( $password ) ){
			$this->messenger->noteError( $words->msgNoPassword );
			return NULL;
		}
		$modelUser	= new Model_User( $this->env );
		$user		= NULL;
		foreach( ['username', 'email'] as $column ){
			/** @var ?Entity_User $user */
			$user	= $modelUser->getByIndex( $column, $username );
			if( NULL !== $user )
				break;
		}
		if( NULL === $user ){
			$this->messenger->noteError( $words->msgInvalidUser );
			return NULL;
		}
		$hookData	= [
			'status'	=> NULL,
			'backend'	=> 'local',
			'username'	=> $user->username,
//			'password'	=> $password,															//  disabled for security
			'userId'	=> $user->userId,
		];
		$this->callHook( 'Auth', 'checkBeforeLogin', $this, $hookData );
		if( $hookData['status'] === FALSE )
			return NULL;

		$role	= Logic_Role::getInstance( $this->env )->get( $user->roleId );
		if( !$role->access ){
			$this->messenger->noteError( $words->msgRoleLocked, $role->title );
			return NULL;
		}

/*		// @deprecated	use role column "access" instead
		// @todo		remove
		$allowedRoles	= $this->moduleConfig->get( 'login.roles' );
		$allowedRoles	= explode( ',', $allowedRoles ? $allowedRoles : "*" );
		if( $allowedRoles !== ["*"] && !in_array( $user->roleId, $allowedRoles ) ){
			$this->messenger->noteError( $words->msgInvalidRole, $role->title );
			return 0;
		}*/
		$insufficientUserStatuses = [
			0	=> $words->msgUserUnconfirmed,
			-1	=> $words->msgUserLocked,
			-2	=> $words->msgUserDisabled,
		];
		foreach( $insufficientUserStatuses as $status => $message ){
			if( $user->status === $status ){
				$this->messenger->noteError( $message );
				return NULL;
			}
		}
		if( !$this->checkPasswordOnLogin( $user, $password ) ){						//  validate password
			$this->messenger->noteError( $words->msgInvalidPassword );
			return NULL;
		}
		return $user;
	}

	/**
	 *	Check given user password against old and newer password storage.
	 *	If newer password store is supported and old password has been found, migration will apply.
	 *
	 *	@access		protected
	 *	@param		Entity_User		$user		User data object
	 *	@param		string			$password	Password to check on login
	 *	@return		bool
	 *	@todo		clean up if support for old password decays
	 *	@todo		reintegrate cleansed lines into login method (if this makes sense)
	 *	@throws		ReflectionException
	 */
	protected function checkPasswordOnLogin( Entity_User $user, string $password ): bool
	{
		$words				= (object) $this->getWords( 'login' );
		$isMinimumVersion	= $this->env->getPhp()->version->isAtLeast( '5.5.0' );
		if( $isMinimumVersion && class_exists( 'Logic_UserPassword' ) ){							//  @todo  remove line if old user password support decays
			$logic			= Logic_UserPassword::getInstance( $this->env );
			$newPassword	= $logic->getActivatableUserPassword( $user, $password );
			if( $logic->hasUserPassword( $user ) ){											//  @todo  remove line if old user password support decays
				if( $logic->validateUserPassword( $user, $password ) )
					return TRUE;
				$newPassword	= $logic->getActivatableUserPassword( $user, $password );
				if( NULL !== $newPassword ){
					$logic->activatePassword( $newPassword );
					$this->messenger->noteNotice( $words->msgNoticePasswordChanged );
					return TRUE;
				}
			}
			else{																					//  @todo  remove whole block if old user password support decays
				$pepper		= $this->moduleConfigUsers->get( 'password.pepper' );
				if( $user->password === md5( $password.$pepper ) ){
					$logic->migrateOldUserPassword( $user, $password );
					return TRUE;
				}
			}
		}
		else{																						//  @todo  remove whole block if old user password support decays
			$pepper		= $this->moduleConfigUsers->get( 'password.pepper' );
			if( $user->password === md5( $password.$pepper ) )
				return TRUE;
		}
		return FALSE;
	}

	/**
	 *	@param		Entity_User		$user
	 *	@param		string			$hash
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function handlePasswordUpdateConfirmGetRequest( Entity_User $user, string $hash ): void
	{
		$words			= (object) $this->getWords( 'update' );
		$logicPassword	= Logic_UserPassword::getInstance( $this->env );

		/** @var ?Entity_User_Password $password */
		$password	= $logicPassword->getWaitingPasswordForPasswordUpdate( $user );
		if( NULL === $password )
			return;
		if( $hash !== sha1( join( '-', [$user->userId, $password->userPasswordId] ) ) )
			return;

		$logicPassword->activatePassword( $password );
		$this->messenger->noteSuccess( $words->msgConfirmed );

		$from	= trim( $this->request->get( 'from', '' ) );
		if( '' !== $from )
			$this->restart( $from );
		$this->restart();
	}

	/**
	 *	@param		Entity_User $user
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	protected function handlePasswordUpdatePostRequest( Entity_User $user ): void
	{
		$words			= (object) $this->getWords( 'update' );
		/** @var Dictionary $input */
		$input			= $this->request->getAllFromSource( 'POST', TRUE );
		$newPassword	= trim( $input->get( 'password', '' ) );
		if( '' === $newPassword ){
			$this->messenger->noteError( $words->msgNoPassword );
			return;
		}
		$pwdMinLength	= (int) $this->moduleConfigUsers->get( 'password.length.min', 0 );
		if( 0 !== $pwdMinLength && strlen( $newPassword ) < $pwdMinLength ){
			$this->messenger->noteError( $words->msgPasswordTooShort, $pwdMinLength );
			return;
		}

		$logicPassword	= Logic_UserPassword::getInstance( $this->env );

		$this->env->getDatabase()->beginTransaction();
		try{
			$password	= $logicPassword->addPassword( $user, $newPassword );
			$data		= [
				'user'			=> $user,
				'firstname'		=> $user->firstname,
				'surname'		=> $user->surname,
				'username'		=> $user->username,
				'userId'		=> $user->userId,
				'password'		=> $newPassword,
				'passwordId'	=> $password->userPasswordId
			];
			$language	= $this->env->getLanguage()->getLanguage();
			$mail		= new Mail_Auth_Local_Update( $this->env, $data );
			$logic		= Logic_Mail::getInstance( $this->env );
			$logic->appendRegisteredAttachments( $mail, $language );
			$logic->sendQueuedMail( $logic->enqueueMail( $mail, $language, $user ) );
			$this->env->getDatabase()->commit();
			$this->messenger->noteSuccess( $words->msgSuccess );
			if( !$this->env->isInLiveMode() )
				$this->messenger->noteNotice( 'Neues Passwort: '.$newPassword." <small><em>(Diese Meldung kommt nicht im Live-Betrieb.)</em></small>" );	//  @todo: remove before going live
			$this->restart();
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $words->msgSendingMailFailed );
			$payload	= ['exception' => $e];
			$this->callHook( 'Env', 'logException', $this, $payload );
		}
		finally{
			$this->env->getDatabase()->rollBack();
		}
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
	 *	Redirects to password update form after login, if current password needs an update.
	 *	This will be the case, if a password has been preset or set by an admin or moderator
	 *	AND the user itself should set a new password, since the current one is not a secret,
	 *	only know by the user.
	 *
	 *	@param		Entity_User|int|string		$userOrId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function redirectAfterLoginIfPasswordUpdateNeeded( Entity_User|int|string $userOrId ): void
	{
		$logicUser		= Logic_User::getInstance( $this->env );
		$logicPassword	= Logic_UserPassword::getInstance( $this->env );
		if( !is_object( $userOrId ) )
			$userOrId	= $logicUser->checkId( $userOrId );
		if( $logicPassword->needsPasswordUpdate( $userOrId ) )
			$this->restart( 'update', TRUE );
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
	 *	@throws		ReflectionException
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	protected function sendRegisterMail(Dictionary $input, int $userId, int $status, ?string $from ): void
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
	 *	@throws		ReflectionException
	 */
	protected function linkCreatedAccountToOAuth( $userId ): void
	{
		if( $this->session->get( 'auth_register_oauth_user_id' ) ){
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
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	protected function registerByPostRequest(): void
	{
		if( !$this->request->getMethod()->isPost() || !$this->request->has( 'save' ) )
			return;

		$modelUser	= new Model_User( $this->env );
		$words		= (object) $this->getWords( 'register' );
		$options	= $this->moduleConfigUsers;

		$input		= $this->evaluateInputOnRegister();
		if( FALSE === $input )
			return;

		$status			= (int) $options->get( 'status.register' );
		$passwordPepper	= trim( $options->get( 'password.pepper' ) );								//  string to pepper password with
		$data	= [
			'roleId'		=> $input->get( 'roleId' ),
			'status'		=> $status,
			'email'			=> $input->get( 'email' ),
			'username'		=> $input->get( 'username' ),
			'password'		=> '',
			'gender'		=> $input->get( 'gender' ),
			'salutation'	=> $input->get( 'salutation' ),
			'firstname'		=> $input->get( 'firstname' ),
			'surname'		=> $input->get( 'surname' ),
			'country'		=> $input->get( 'country' ),
			'postcode'		=> $input->get( 'postcode' ),
			'city'			=> $input->get( 'city' ),
			'street'		=> $input->get( 'street' ),
			'phone'			=> $input->get( 'phone' ),
			'fax'			=> $input->get( 'fax' ),
			'createdAt'		=> time(),
		];

		$this->env->getDatabase()->beginTransaction();
		$from		= $this->request->get( 'from' );
		$forward	= './auth/local/login'.( $from ? '?from='.$from : '' );
		try{
			$userId		= $modelUser->add( $data );
			/** @var Entity_User $user */
			$user		= $modelUser->get( $userId );
			$logic	= Logic_UserPassword::getInstance( $this->env );
			$userPassword	= $logic->addPassword( $user, $input->get( 'password' ) );
			$logic->activatePassword( $userPassword );

			$this->sendRegisterMail( $input, $userId, $status, $from );
			$this->linkCreatedAccountToOAuth( $userId );

			$this->env->getDatabase()->commit();
			$this->messenger->noteSuccess( $words->msgSuccess );
			if( !$status )
				$this->messenger->noteNotice( $words->msgNoticeConfirm );
			$forward	= './auth/local/confirm'.( $from ? '?from='.$from : '' );
			$this->restart( $forward );
		}
		catch( Exception $e ){
//			$this->messenger->noteFailure( $words->msgSendingMailFailed );
			$this->messenger->noteFailure( 'Fehler aufgetreten: '.$e->getMessage() );
			$payload	= ['exception' => $e];
			$this->callHook( 'Env', 'logException', $this, $payload );
		}
		$this->env->getDatabase()->rollBack();
	}

	protected function rememberUserInCookie( Entity_User $user ): void
	{
		$expires	= strtotime( "+2 years" ) - time();
		$passwordHash	= md5( sha1( $user->password ) );											//  hash password using SHA1 and MD5
		if( $this->env->getPhp()->version->isAtLeast( '5.5.0' ) )											//  for PHP 5.5.0+
			$passwordHash	= password_hash( $user->password, PASSWORD_BCRYPT );					//  hash password using BCRYPT
		$this->cookie->set( 'auth_remember', TRUE, $expires );
		$this->cookie->set( 'auth_remember_id', $user->userId, $expires );
		$this->cookie->set( 'auth_remember_pw', $passwordHash, $expires );
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
	 */
	protected function tryLoginByCookie(): void
	{
		if( $this->logic->isIdentified() )															//  no need to try
			return;
		if( !$this->cookie->get( 'auth_remember' ) )											//  autologin has not been activated
			return;

		$userId			= (int) $this->cookie->get( 'auth_remember_id' );							//  get user ID from cookie
		$passwordHash	= (string) $this->cookie->get( 'auth_remember_pw' );						//  get hashed password from cookie
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
				$from	= !preg_match( "/auth\/logout/", $from ) ? $from : '';				//  exclude logout from redirect request
				$this->restart( './'.$from );													//  restart (or go to redirect URL)
			}
		}
		$this->cookie->remove( 'auth_remember' );
		$this->cookie->remove( 'auth_remember_id' );
		$this->cookie->remove( 'auth_remember_pw' );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	protected function tryLoginByPostRequest(): void
	{
		$words		= (object) $this->getWords( 'login' );
		$username	= trim( $this->request->get( 'login_username', '' ) );
		$password	= trim( $this->request->get( 'login_password', '' ) );
		$from		= trim( $this->request->get( 'from', '' ) );

		if( !$this->request->getMethod()->isPost() || !$this->request->has( 'doLogin' ) )
			return;
		if( 0 === strlen( $username ) || 0 === strlen( $password ) )
			return;

		if( $this->useCsrf ){
			$controller	= new Controller_Csrf( $this->env );
			$controller->checkToken();
		}
		try{
			/** @var ?Entity_User $user */
			$user	= $this->authenticateUserByCredentials( $username, $password );
			if( NULL === $user ){
				if( 0 !== strlen( $from ) )
					$this->restart( $from.'?login='.$username );
				$this->restart( 'login?username='.$username, TRUE );
			}
			$modelUser	= new Model_User( $this->env );
			$modelUser->edit( $user->userId, ['loggedAt' => time()] );
			$this->messenger->noteSuccess( $words->msgSuccess );

			/** @var Entity_User $user */
			$user	= $modelUser->get( $user->userId );
			$this->session->set( Logic_Authentication::$sessionKeyAuthUserId, $user->userId );
			$this->session->set( Logic_Authentication::$sessionKeyAuthRoleId, $user->roleId );
			$logicAuth	= $this->env->getLogic()->get( 'Authentication' );
			$logicAuth->setAuthenticatedUser( $user, $password );
			if( $this->request->get( 'login_remember' ) )
				$this->rememberUserInCookie( $user );

			$this->redirectAfterLoginIfPasswordUpdateNeeded( $user );
			$this->redirectAfterLogin();
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $e->getMessage() );
		}
	}
}
