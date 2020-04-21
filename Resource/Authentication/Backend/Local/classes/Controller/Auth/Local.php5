<?php
class Controller_Auth_Local extends CMF_Hydrogen_Controller {

	protected $config;
	protected $request;
	protected $session;
	protected $cookie;
	protected $messenger;
	protected $modules;
	protected $useCsrf;
	protected $useOauth2;
	protected $moduleConfig;
	protected $moduleConfigAuth;
	protected $moduleConfigUsers;
	protected $limiter;
	protected $logic;

	public function __onInit(){
		$this->config		= $this->env->getConfig();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->cookie		= new Net_HTTP_Cookie( parse_url( $this->env->url, PHP_URL_PATH ) );
		if( isset( $this->env->version ) )
			if( version_compare( $this->env->version, '0.8.6.5', '>=' ) )
				$this->cookie	= $this->env->getCookie();
		$this->messenger	= $this->env->getMessenger();
		$this->modules		= $this->env->getModules();
		$this->useCsrf		= $this->modules->has( 'Security_CSRF' );
		$this->logic		= $this->env->getLogic()->get( 'Authentication_Backend_Local' );

		$this->useOauth2	= FALSE;																//  assume that OAuth2 is not installed or registers as login tab
		if( $this->modules->has( 'Resource_Authentication_Backend_OAuth2' ) ){						//  OAuth2 is installed
			$module		= $this->modules->get( 'Resource_Authentication_Backend_OAuth2' );			//  get module object
			if( $module->isActive && isset( $module->config['loginMode'] ) )						//  module is enabled and login mode is defined
				$this->useOauth2	= $module->config['loginMode']->value === 'buttons';			//  use OAuth2 in local login only in buttons mode
		}

		$this->moduleConfig			= $this->config->getAll( 'module.resource_authentication_backend_local.', TRUE );
		$this->moduleConfigAuth		= $this->config->getAll( 'module.resource_authentication.', TRUE );
		$this->moduleConfigUsers	= $this->config->getAll( 'module.resource_users.', TRUE );
		if( $this->modules->has( 'Resource_Limiter' ) )
//			if( $this->modules->get( 'Resource_Limiter' )->isActive )				// @todo apply this line here and anywhere else
				$this->limiter	= Logic_Limiter::getInstance( $this->env );
		$this->addData( 'limiter', $this->limiter );
		$this->addData( 'useCsrf', $this->useCsrf );
		$this->addData( 'useOauth2', $this->useOauth2 );
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
		$words				= (object) $this->getWords( 'login' );
		$isMinimumVersion	= $this->env->getPhp()->version->isAtLeast( '5.5.0' );
		if( $isMinimumVersion && class_exists( 'Logic_UserPassword' ) ){							//  @todo  remove line if old user password support decays
			$logic			= Logic_UserPassword::getInstance( $this->env );
			$newPassword	= $logic->getActivableUserPassword( $user->userId, $password );
			if( $logic->hasUserPassword( $user->userId ) ){											//  @todo  remove line if old user password support decays
				if( $logic->validateUserPassword( $user->userId, $password ) )
					return TRUE;
				$newPassword	= $logic->getActivableUserPassword( $user->userId, $password );
				if( $newPassword ){
					$logic->activatePassword( $newPassword->userPasswordId );
					$this->messenger->noteNotice( $words->msgNoticePasswordChanged );
					return TRUE;
				}
			}
			else{																					//  @todo  remove whole block if old user password support decays
				$pepper		= $this->moduleConfigUsers->get( 'password.pepper' );
				if( $user->password === md5( $password.$pepper ) ){
					$logic->migrateOldUserPassword( $user->userId, $password );
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
 	 *	@todo		send mail to user after confirmation with user data
	 */
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
					$result	= $this->callHook( 'Auth', 'afterConfirm', $this, array(
						'userId'	=> $user->userId,
						'roleId'	=> $user->roleId,
						'from'		=> $from,
					) );
					if( 1 ){
						$this->messenger->noteSuccess( $words->msgSuccessAutoLogin );
						$this->session->set( 'userId', $user->userId );
						$this->session->set( 'roleId', $user->roleId );
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

	public function index(){
		if( !$this->session->has( 'userId' ) )
			return $this->redirect( 'auth', 'login' );											// @todo replace redirect

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

	/**
	 *	@todo implement username parameter to be used (not the case right now)
	 */
	public function login( $username = NULL ){
		if( $this->session->has( 'userId' ) )
			$this->redirectAfterLogin();

		$this->session->set( 'authBackend', 'Local' );

		$this->tryLoginByCookie();

		$words		= (object) $this->getWords( 'login' );
		$username	= trim( $this->request->get( 'login_username' ) );
		$password	= trim( $this->request->get( 'login_password' ) );
		$from		= $this->request->get( 'from' );

		if( $this->request->getMethod()->isPost() && $this->request->has( 'doLogin' ) ) {
			if( $this->useCsrf ){
				$controller	= new Controller_Csrf( $this->env );
				$controller->checkToken();
			}
			try{
				$userId	= $this->authenticateUserByCredentials( $username, $password );
				if( !$userId ){
					if( $from )
						$this->restart( $from.'?login='.$username );
					$this->restart( 'login?username='.$username, TRUE );
				}
				$modelUser	= new Model_User( $this->env );
				$modelUser->edit( $user->userId, array( 'loggedAt' => time() ) );
				$this->messenger->noteSuccess( $words->msgSuccess );

				$user	= $modelUser->get( $userId );
				$this->session->set( 'userId', $user->userId );
				$this->session->set( 'roleId', $user->roleId );
				$logicAuth	= $this->env->getLogic()->get( 'Authentication' );
				$logicAuth->setAuthenticatedUser( $user, $password );
				if( $this->request->get( 'login_remember' ) )
					$this->rememberUserInCookie( $user );
				$this->redirectAfterLogin();
			}
			catch( Exception $e ){
				$this->messenger->noteFailure( $e->getMessage() );
			}
		}

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

	public function logout( $redirectController = NULL, $redirectAction = NULL ){
		$words		= (object) $this->getWords( 'logout' );
		$logicAuth	= $this->env->getLogic()->get( 'Authentication' );
		if( $this->session->has( 'userId' ) ){
			$this->env->getCaptain()->callHook( 'Auth', 'onBeforeLogout', $this, array(
				'userId'	=> $this->session->get( 'userId' ),
				'roleId'	=> $this->session->get( 'roleId' ),
			) );
			$this->session->remove( 'userId' );
			$this->session->remove( 'roleId' );
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

	public function password(){
		$words			= (object) $this->getWords( 'password' );
		$modelUser		= new Model_User( $this->env );

		$options		= $this->moduleConfigUsers;
		$passwordPepper	= trim( $options->get( 'password.pepper' ) );								//  string to pepper password with

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
					$mail		= new Mail_Auth_Local_Password( $this->env, $data );
					$logic		= Logic_Mail::getInstance( $this->env );
					$logic->appendRegisteredAttachments( $mail, $language );
					$logic->sendQueuedMail( $logic->enqueueMail( $mail, $language, $user ) );
					if( class_exists( 'Logic_UserPassword' ) ){										//  @todo  remove line if old user password support decays
						$logic	= Logic_UserPassword::getInstance( $this->env );
						$userPasswordId	= $logic->addPassword( $user->userId, $password );
					}
					else{																			//  @todo  remove whole block if old user password support decays
						$crypt		= md5( $password.$passwordPepper );
						$modelUser->edit( $user->userId, array( 'password' => $crypt ) );

					}
					$this->env->getDatabase()->commit();
					$this->messenger->noteSuccess( $words->msgSuccess );
	//				$this->messenger->noteNotice( 'Neues Passwort: '.$password." <small><em>(Diese Meldung kommt nicht im Live-Betrieb.)</em></small>" );	//  @todo: remove before going live
					$this->restart( './auth/local/login?login_username='.$user->username );
				}
				catch( Exception $e ){
					$this->messenger->noteFailure( $words->msgSendingMailFailed );
					$this->callHook( 'Env', 'logException', $this, array( 'exception' => $e ) );
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

		$roleDefault	= $modelRole->getByIndex( 'register', 128, 'roleId' );
		$rolesAllowed	= array();
		foreach( $modelRole->getAllByIndex( 'register', array( 64, 128 ) ) as $role )
			$rolesAllowed[]	= $role->roleId;

		$input			= $this->request->getAllFromSource( 'post' );
		$options		= $this->moduleConfigUsers;

		$nameMinLength	= $options->get( 'name.length.min' );
		$nameMaxLength	= $options->get( 'name.length.max' );
		$nameRegExp		= $options->get( 'name.preg' );
		$pwdMinLength	= $options->get( 'password.length.min' );
		$needsEmail		= $options->get( 'email.mandatory' );
		$needsFirstname	= $options->get( 'firstname.mandatory' );
		$needsSurname	= $options->get( 'surname.mandatory' );
		$needsTac		= $options->get( 'tac.mandatory' );
		$status			= (int) $options->get( 'status.register' );
		$passwordPepper	= trim( $options->get( 'password.pepper' ) );								//  string to pepper password with

		$roleId		= $roleDefault->roleId;															//  use default register role if none given
		if( $this->request->has( 'roleId' ) && trim( $input->get( 'roleId' ) ) ){
			if( in_array( (int) $this->request->get( 'roleId' ), $rolesAllowed ) ){
				$roleId		= $input->get( 'roleId' );
			}
		}

		$username	= $input->get( 'username' );
		$password	= $input->get( 'password' );
		$email		= $input->get( 'email' );

		$errors	= $this->messenger->gotError();
		if( $this->request->has( 'save' ) ){
			$result	= $this->callHook( 'Auth', 'checkBeforeRegister', $this, $input );
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
					'password'		=> md5( $password.$passwordPepper ),							//  @todo  remove if old user password support decays
					'gender'		=> $input['gender'],
					'salutation'	=> $input['salutation'],
					'firstname'		=> $input['firstname'],
					'surname'		=> $input['surname'],
					'country'		=> $input['country'],
					'postcode'		=> $input['postcode'],
					'city'			=> $input['city'],
					'street'		=> $input['street'],
					'phone'			=> $input['phone'],
					'fax'			=> $input['fax'],
					'createdAt'		=> time(),
				);

				if( class_exists( 'Logic_UserPassword' ) ){											//  @todo  remove line if old user password support decays
					$data['password']	= '';
				}


				$this->env->getDatabase()->beginTransaction();
				$from		= $this->request->get( 'from' );
				$forward	= './auth/local/login'.( $from ? '?from='.$from : '' );
				try{
					$userId		= $modelUser->add( $data );
					if( class_exists( 'Logic_UserPassword' ) ){										//  @todo  remove line if old user password support decays
						$logic	= Logic_UserPassword::getInstance( $this->env );
						$userPasswordId	= $logic->addPassword( $userId, $password );
						$logic->activatePassword( $userPasswordId );
					}

					if( !$status ){
						$data				= $input->getAll();
						$data['from']		= $from;
						$data['pak']		= md5( 'pak:'.$userId.'/'.$username.'&'.$passwordPepper );

						$language	= $this->env->getLanguage()->getLanguage();
						$user		= $modelUser->get( $userId );
						$mail		= new Mail_Auth_Local_Register( $this->env, $data );
						$logic		= Logic_Mail::getInstance( $this->env );
						$logic->appendRegisteredAttachments( $mail, $language );
						$mailId		= $logic->enqueueMail( $mail, $language, $user );
						$logic->sendQueuedMail( $mailId );
						$forward	= './auth/local/confirm'.( $from ? '?from='.$from : '' );
						if( $this->session->get( 'auth_register_oauth_user_id' ) ){
							$modelOauthUser	= new Model_Oauth_User( $this->env );
							$modelOauthUser->add( array(
								'oauthProviderId'	=> $this->session->get( 'auth_register_oauth_provider_id' ),
								'oauthId'			=> $this->session->get( 'auth_register_oauth_user_id' ),
								'localUserId'		=> $userId,
								'timestamp'			=> time(),
							) );
							$this->session->remove( 'auth_register_oauth_provider_id' );
							$this->session->remove( 'auth_register_oauth_provider' );
							$this->session->remove( 'auth_register_oauth_user_id' );
							$this->session->remove( 'auth_register_oauth_data' );
						}
					}
					$this->env->getDatabase()->commit();
					$this->messenger->noteSuccess( $words->msgSuccess );
					if( !$status )
						$this->messenger->noteNotice( $words->msgNoticeConfirm );
					$this->restart( $forward );
				}
				catch( Exception $e ){
//					$this->messenger->noteFailure( $words->msgSendingMailFailed );
					$this->messenger->noteFailure( 'Fehler aufgetreten: '.$e->getMessage() );
					$this->callHook( 'Env', 'logException', $this, array( 'exception' => $e ) );
				}
				$this->env->getDatabase()->rollBack();
			}
		}
		if( $this->session->get( 'auth_register_oauth_user_id' ) ){
			$fields	= array( 'username', 'email', 'gender', 'firstname', 'surname', 'street', 'postcode', 'city', 'phone' );
			foreach( $fields as $field )
				if( !isset( $input[$field] ) || !strlen( trim( $input[$field] ) ) )
					$input[$field]	= $this->session->get( 'auth_register_oauth_'.$field );
		}
		foreach( $input as $key => $value )
			$input[$key]	= htmlentities( $value, ENT_COMPAT, 'UTF-8' );
		if( !$input->get( 'country' ) && $this->env->getLanguage()->getLanguage() == 'de' )
			$input->set( 'country', 'DE' );

		$this->addData( 'user', $input );
		$this->addData( 'from', $this->request->get( 'from' ) );									//  forward redirect URL to form action
		$this->addData( 'countries', $this->env->getLanguage()->getWords( 'countries' ) );
	}

	//  --  PROTECTED  --  //

	protected function authenticateUserByCredentials( $username, $password ){
		$words		= (object) $this->getWords( 'login' );
		if( !strlen( $username ) ){
			$this->messenger->noteError( $words->msgNoUsername );
			return 0;
		}
		if( !trim( $password ) ){
			$this->messenger->noteError( $words->msgNoPassword );
			return 0;
		}
		$modelUser	= new Model_User( $this->env );
		$modelRole	= new Model_Role( $this->env );
		foreach( array( 'username', 'email' ) as $column ){
			if( ( $user = $modelUser->getByIndex( $column, $username ) ) )
				break;
		}
		if( !$user ){
			$this->messenger->noteError( $words->msgInvalidUser );
			return 0;
		}
		$hookData	= (object) array(
			'status'	=> NULL,
			'backend'	=> 'local',
			'username'	=> $user ? $user->username : $username,
//			'password'	=> $password,															//  disabled for security
			'userId'	=> $user ? $user->userId : 0,
		);
		$this->callHook( 'Auth', 'checkBeforeLogin', $this, $hookData );
		if( $hookData->status === FALSE )
			return 0;

		$role	= $modelRole->get( $user->roleId );
		if( !$role->access ){
			$this->messenger->noteError( $words->msgRoleLocked, $role->title );
			return 0;
		}

/*		// @deprecated	use role column "access" instead
		// @todo		remove
		$allowedRoles	= $this->moduleConfig->get( 'login.roles' );
		$allowedRoles	= explode( ',', $allowedRoles ? $allowedRoles : "*" );
		if( $allowedRoles !== array( "*" ) && !in_array( $user->roleId, $allowedRoles ) ){
			$this->messenger->noteError( $words->msgInvalidRole, $role->title );
			return 0;
		}*/
		$insufficientUserStatuses = array(
			0	=> $words->msgUserUnconfirmed,
			-1	=> $words->msgUserLocked,
			-2	=> $words->msgUserDisabled,
		);
		foreach( $insufficientUserStatuses as $status => $message ){
			if( (int) $user->status === $status ){
				$this->messenger->noteError( $message );
				return 0;
			}
		}
		if( !$this->checkPasswordOnLogin( $user, $password ) ){						//  validate password
			$this->messenger->noteError( $words->msgInvalidPassword );
			return 0;
		}
		return $user->userId;
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
	protected function redirectAfterLogin( $controller = NULL, $action = NULL ){
		if( $controller )																			//  a redirect contoller has been argumented
			$this->restart( $controller.( $action ? '/'.$action : '' ) );							//  redirect to controller and action if given
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
	 *	3. Request paramter 'from'
	 *	4. Forward path of this auth module
	 *	5. Go to index (empty path)
	 *	ATM this is the same method for each auth module.
	 *	@access		protected
	 *	@return		void
	 *	@todo		find a way to generalize this method into some base auth adapter controller or logic
	 */
	protected function redirectAfterLogout( $controller = NULL, $action = NULL ){
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

	protected function rememberUserInCookie( $user ){
		$expires	= strtotime( "+2 years" ) - time();
		$passwordHash	= md5( sha1( $user->password ) );											//  hash password using SHA1 and MD5
		if( $this->env->getPhp()->version->isAtLeast( '5.5.0' ) )											//  for PHP 5.5.0+
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
					if( $this->env->getPhp()->version->isAtLeast( '5.5.0' ) )								//  for PHP 5.5.0+
						$passwordMatch	= password_verify( $user->password, $password );			//  verify password hash
					if( $passwordMatch ){															//  password from cookie is matching
						$modelUser->edit( $user->userId, array( 'loggedAt' => time() ) );			//  note login time in database
						$this->session->set( 'userId', $user->userId );								//  set user ID in session
						$this->session->set( 'roleId', $user->roleId );								//  set user role in session
						$this->logic->setAuthenticatedUser( $user );
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
