<?php
class Controller_Auth extends CMF_Hydrogen_Controller {

	protected $config;
	protected $request;
	protected $session;
	protected $messenger;

	public function __onInit(){
		$this->config		= $this->env->getConfig();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
	}

	static public function ___onPageApplyModules( CMF_Hydrogen_Environment_Abstract $env, $context, $module ){
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

	public function confirm(){
		$words		= (object) $this->getWords( 'confirm' );

		if( $this->request->has( 'confirm_code' ) ){
			$code			= $this->request->get( 'confirm_code' );
			$passwordSalt	= trim( $this->config->get( 'module.resource.users.password.salt' ) );						//  string to salt password with

			$modelUser		= new Model_User( $this->env );
			$users			= $modelUser->getAllByIndex( 'status', 0 );
			foreach( $users as $user ){
				$pak	= md5( 'pak:'.$user->userId.'/'.$user->username.'&'.$passwordSalt );
				if( $code == $pak ){
					$modelUser->edit( $user->userId, array( 'status' => 1 ) );
					$this->messenger->noteSuccess( $words->msgSuccess );
					$this->restart( './auth/login/'.$user->username );
				}
			}
			$this->messenger->noteError( $words->msgInvalidCode );
		}
	}

	public function login( $username = NULL ){
		$words		= (object) $this->getWords( 'login' );

		if( $this->session->has( 'userId' ) )
			return $this->redirect( 'auth', 'loginInside' );

		if( $this->request->has( 'doLogin' ) ) {
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
						$from	= $this->request->get( 'from' );									//  get redirect URL from request if set
						$from	= !preg_match( "/auth\/logout/", $from ) ? $from : '';				//  exclude logout from redirect request
						$this->restart( './'.$from );												//  restart (or go to redirect URL)
					}
				}
			}
		}
		$this->addData( 'from', $this->request->get( 'from' ) );									//  forward redirect URL to form action
		$this->addData( 'login_username', $username );
	}

	public function logout( $redirectController = NULL, $redirectAction = NULL ){
		$words		= $this->env->getLanguage()->getWords( 'auth' );
		$message	= $words['logout']['msgSuccess'];
		if( $this->session->remove( 'userId' ) ){
			$this->env->getMessenger()->noteSuccess( $message );
			if( $this->request->has( 'autoLogout' ) )
				$this->env->getMessenger()->noteNotice( $words['logout']['msgAutoLogout'] );
			$this->session->remove( 'userId' );
			$this->session->remove( 'roleId' );
			if( $this->env->getConfig()->get( 'module.resource_auth.logout.clearSession' ) )
				session_destroy();
		}
		$redirectTo	= '';																			//  assume empty redirect URL
		if( $redirectController && $redirectAction )												//  both redirect controller and action given
			$redirectTo	= $redirectController.'/'.$redirectAction;									//  generate redirect URL
		else if( $redirectController )																//  or only redirect controller given
			$redirectTo	= $redirectController;														//  generate redirect URL
		else if( $this->request->get( 'from' ) )													//  or redirect URL given via parameter "from"
			$redirectTo	= $this->request->get( 'from' );											//  take redirect URL from parameter
		$this->restart( './'.$redirectTo );															//  restart (to redirect URL if set)
	}

	public function loginInside(){}

	public function password(){
		$words		= (object) $this->getWords( 'password' );

		if( $this->request->has( 'password_email' ) ){
			if( ( $email = $this->request->get( 'password_email' ) ) ){
				$modelUser	= new Model_User( $this->env );
				$user		= $modelUser->getByIndex( 'email', $email );
				if( $user ){
					$randomizer	= new Alg_Randomizer();
					$randomizer->configure( TRUE, TRUE, TRUE, FALSE, 0 );
					$password	= $randomizer->get( 8 );
					$modelUser->edit( $user->userId, array( 'password' => md5( $password ) ) );
//					$this->messenger->noteNotice( 'Neues Passwort: '.$password." <small><em>(Diese Meldung kommt nicht im Live-Betrieb.)</em></small>" );	//  @todo: remove before going live
					$this->messenger->noteSuccess( $words->msgSuccess );
					$this->restart( './auth/login' );
				}
				$this->messenger->noteError( $words->msgInvalidEmail );
			}
			$this->messenger->noteError( $words->msgNoEmail );
		}
	}

	public function register(){
		$words		= (object) $this->getWords( 'register' );

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
		if( $this->request->get( 'saveUser' ) ){
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
				$userId		= $modelUser->add( $data );
				$this->messenger->noteSuccess( $words->msgSuccess );

				if( !$status ){
					$pak		= md5( 'pak:'.$userId.'/'.$username.'&'.$passwordSalt );
					$data	= $input->getAll();
					$data['pak']		= $pak;
					$data['password']	= $password;
					$mail		= new Mail_Auth_Register( $this->env, $data );
					$mail->sendTo( $modelUser->get( $userId ) );
					$this->messenger->noteNotice( $words->msgNoticeConfirm );
					$this->restart( './auth/confirm' );
				}
				$this->restart( './auth/login' );
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
	}
}
?>
