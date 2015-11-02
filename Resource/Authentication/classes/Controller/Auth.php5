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
//		$this->cookie		= new Net_HTTP_PartitionCookie( "hydrogen", "/" );
		$this->cookie		= new Net_HTTP_Cookie( "/" );
		$this->messenger	= $this->env->getMessenger();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_authentication.', TRUE );
		$this->addData( 'useCsrf', $this->useCsrf = $this->env->getModules()->has( 'Security_CSRF' ) );
	}

	static public function ___onPageApplyModules( CMF_Hydrogen_Environment_Abstract $env, $context, $module, $data = array() ){
		$userId	= (int) $env->getSession()->get( 'userId' );															//  get ID of current user (or zero)
		$script	= 'Auth.init('.$userId.');';																			//  initialize Auth class with user ID
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

		if( strlen( trim( (string) $code ) ) ){
			$passwordSalt	= trim( $this->config->get( 'module.resource.users.password.salt' ) );						//  string to salt password with
			$modelUser		= new Model_User( $this->env );
			$users			= $modelUser->getAllByIndex( 'status', 0 );
			foreach( $users as $user ){
				$pak	= md5( 'pak:'.$user->userId.'/'.$user->username.'&'.$passwordSalt );
				if( $pak === $code ){
					$modelUser->edit( $user->userId, array( 'status' => 1 ) );
					$this->messenger->noteSuccess( $words->msgSuccess );
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

			if( !$this->messenger->gotError() ){
				$modelUser	= new Model_User( $this->env );
				$modelRole	= new Model_Role( $this->env );
				$user		= $modelUser->getByIndex( 'username', $username );
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
						if( $this->request->get( 'login_remember' ) ){
							$expires	= strtotime( "+2 years" ) - time();
							$passwordHash	= md5( sha1( $user->password ) );						//  hash password using SHA1 and MD5
							if( version_compare( PHP_VERSION, '5.5.0' ) >= 0 )						//  for PHP 5.5.0+
								$passwordHash	= password_hash( $user->password );					//  hash password using BCRYPT
							$this->cookie->set( 'auth_remember', TRUE, $expires );
							$this->cookie->set( 'auth_remember_id', $user->userId, $expires );
							$this->cookie->set( 'auth_remember_pw', $passwordHash, $expires );
						}
						$from	= $this->request->get( 'from' );									//  get redirect URL from request if set
						$from	= !preg_match( "/auth\/logout/", $from ) ? $from : '';				//  exclude logout from redirect request
						$this->restart( './auth?from='.$from );												//  restart (or go to redirect URL)
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
	}

	public function logout( $redirectController = NULL, $redirectAction = NULL ){
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

	public function password(){
		$words			= (object) $this->getWords( 'password' );
		$modelUser		= new Model_User( $this->env );

		$options		= $this->config->getAll( 'module.resource_users.', TRUE );
		$passwordSalt	= trim( $options->get( 'password.salt' ) );						//  string to salt password with

		if( $this->request->has( 'sendPassword' ) ){
			if( !( $email = $this->request->get( 'password_email' ) ) ){
				$this->messenger->noteError( $words->msgNoEmail );
				$this->restart( 'password', TRUE );
			}
			if( !( $user = $modelUser->getByIndex( 'email', $email ) ) ){
				$this->messenger->noteError( $words->msgInvalidEmail );
			}
			else{
				$randomizer	= new Alg_Randomizer();
				$randomizer->configure( TRUE, TRUE, TRUE, FALSE, 0 );
				$password	= $randomizer->get( 8 );

				$this->env->getDatabase()->beginTransaction();
				try{
					$data		= array(
						'firstname'	=> $user->firstname,
						'surname'	=> $user->surname,
						'username'	=> $user->username,
						'password'	=> $password,
					);
					$language	= $this->env->getLanguage()->getLanguage();
					$mail		= new Mail_Auth_Password( $this->env, $data );
					$logic		= new Logic_Mail( $this->env );
					$logic->appendRegisteredAttachments( $mail, $language );
					$logic->sendQueuedMail( $logic->enqueueMail( $mail, $language, $user ) );
					$modelUser->edit( $user->userId, array( 'password' => md5( $passwordSalt.$password ) ) );
					$this->env->getDatabase()->commit();
					$this->messenger->noteSuccess( $words->msgSuccess );
	//				$this->messenger->noteNotice( 'Neues Passwort: '.$password." <small><em>(Diese Meldung kommt nicht im Live-Betrieb.)</em></small>" );	//  @todo: remove before going live
					$this->restart( './auth/login?login_username='.$user->username );
				}
				catch( Exception $e ){
					$this->messenger->noteFailure( $words->msgSendingMailFailed, $e->getMessage() );
				}
				$this->env->getDatabase()->rollBack();
			}
		}
		$this->addData( 'password_email', $this->request->get( 'password_email' ) );
	}

	public function register(){
		$words		= (object) $this->getWords( 'register' );

		if( !$this->moduleConfig->get( 'register' ) ){
			$this->messenger->noteError( $words->msgRegistrationClosed );
			$this->restart( $this->request->get( 'from' ) );
		}

		$modelUser	= new Model_User( $this->env );
		$modelRole	= new Model_Role( $this->env );

		$roleDefaultId	= $modelRole->getByIndex( 'register', 128, 'roleId' );
		$rolesAllowed	= array();
		foreach( $modelRole->getAllByIndex( 'register', array( 64, 128 ) ) as $role )
			$rolesAllowed[]	= $role->roleId;

		$input			= $this->request->getAllFromSource( 'post' );
		$options		= $this->config->getAll( 'module.resource_users.', TRUE );

		$nameMinLength	= $options->get( 'name.length.min' );
		$nameMaxLength	= $options->get( 'name.length.max' );
		$nameRegExp		= $options->get( 'name.preg' );
		$pwdMinLength	= $options->get( 'password.length.min' );
		$needsEmail		= $options->get( 'email.mandatory' );
		$needsFirstname	= $options->get( 'firstname.mandatory' );
		$needsSurname	= $options->get( 'surname.mandatory' );
		$needsTac		= $options->get( 'tac.mandatory' );
		$status			= (int) $options->get( 'status.register' );
		$passwordSalt	= trim( $options->get( 'password.salt' ) );						//  string to salt password with

		$roleId		= $this->request->has( 'roleId' ) ? $input->get( 'roleId' ) : $roleDefaultId;			//  use default register role if none given
		$username	= $input->get( 'username' );
		$password	= $input->get( 'password' );
		$email		= $input->get( 'email' );

		$errors	= $this->messenger->gotError();
		if( $this->request->has( 'save' ) ){
			if( !in_array( $roleId, $rolesAllowed ) )
				$this->messenger->noteError( $words->msgRoleInvalid );
			if( empty( $username ) )
				$this->messenger->noteError( $words->msgNoUsername );
			else if( $modelUser->countByIndex( 'username', $username ) )
				$this->messenger->noteError( $words->msgUsernameExisting, $username );
			else if( $nameRegExp )
				if( !Alg_Validation_Predicates::isPreg( $username, $nameRegExp ) )
					$this->messenger->noteError( $words->msgUsernameInvalid, $username, $nameRegExp );
			if( empty( $password ) )
				$this->messenger->noteError( $words->msgNoPassword );
			else if( $pwdMinLength && strlen( $password ) < $pwdMinLength )
				$this->messenger->noteError( $words->msgPasswordTooShort, $pwdMinLength );
			if( $needsEmail && empty( $email ) )
				$this->messenger->noteError( $words->msgNoEmail);
			else if( !empty( $email ) && $modelUser->countByIndex( 'email', $email ) )
				$this->messenger->noteError( $words->msgEmailExisting, $email );
			if( $needsFirstname && empty( $input['firstname'] ) )
				$this->messenger->noteError( $words->msgNoFirstname );
			if( $needsSurname && empty( $input['surname'] ) )
				$this->messenger->noteError( $words->msgNoSurname );
			if( $needsTac &&  empty( $input['accept_tac'] ) )
				$this->messenger->noteError( $words->msgTermsNotAccepted  );

			if( $this->messenger->gotError() - $errors == 0 ){
				$data	= array(
					'roleId'		=> $roleId,
					'status'		=> $status,
					'email'			=> $email,
					'username'		=> $username,
					'password'		=> md5( $passwordSalt.$password ),
					'gender'		=> $input['gender'],
					'salutation'	=> $input['salutation'],
					'firstname'		=> $input['firstname'],
					'surname'		=> $input['surname'],
					'postcode'		=> $input['postcode'],
					'city'			=> $input['city'],
					'street'		=> $input['street'],
					'number'		=> $input['number'],
					'phone'			=> $input['phone'],
					'fax'			=> $input['fax'],
					'createdAt'		=> time(),
				);
				$this->env->getDatabase()->beginTransaction();
				$from		= $this->request->get( 'from' );
				$forward	= './auth/login'.( $from ? '?from='.$from : '' );
				try{
					$userId		= $modelUser->add( $data );

					if( !$status ){
						$data				= $input->getAll();
						$data['from']		= $from;
						$data['pak']		= md5( 'pak:'.$userId.'/'.$username.'&'.$passwordSalt );

						$language	= $this->env->getLanguage()->getLanguage();
						$user		= $modelUser->get( $userId );
						$mail		= new Mail_Auth_Register( $this->env, $data );
						$logic		= new Logic_Mail( $this->env );
						$logic->appendRegisteredAttachments( $mail, $language );
						$logic->sendQueuedMail( $logic->enqueueMail( $mail, $language, $user ) );
						$forward	= './auth/confirm'.( $from ? '?from='.$from : '' );
					}
					$this->env->getDatabase()->commit();
					$this->messenger->noteSuccess( $words->msgSuccess );
					if( !$status )
						$this->messenger->noteNotice( $words->msgNoticeConfirm );
					$this->restart( $forward );
				}
				catch( Exception $e ){
					$this->messenger->noteFailure( $words->msgSendingMailFailed );
					// @todo log errors, but how and were without general logging system?
				}
				$this->env->getDatabase()->rollBack();
			}
		}
		if( $this->session->get( 'auth_register_oauth_user_id' ) ){
			if( !$input->has( 'username' ) )
				$input->set( 'username', $this->session->get( 'auth_register_oauth_username' ) );
			if( empty( $input['email'] ) )
				$input['email']	= $this->session->get( 'auth_register_oauth_email' );
		}

		foreach( $input as $key => $value )
			$input[$key]	= htmlentities( $value, ENT_COMPAT, 'UTF-8' );
		$this->addData( 'user', $input );
		$this->addData( 'from', $this->request->get( 'from' ) );									//  forward redirect URL to form action
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
?>
