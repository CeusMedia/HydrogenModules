<?php
class Controller_Auth extends CMF_Hydrogen_Controller {

	public function confirm(){
		$config		= $this->env->getConfig();
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= $this->getWords( 'confirm' );

		if( $request->has( 'confirm_code' ) ){
			$code			= $request->get( 'confirm_code' );
			$passwordSalt	= trim( $config->get( 'module.users.password.salt' ) );						//  string to salt password with

			$modelUser		= new Model_User( $this->env );
			$users			= $modelUser->getAllByIndex( 'status', 0 );
			foreach( $users as $user ){
				$pak	= md5( 'pak:'.$user->userId.'/'.$user->username.'&'.$passwordSalt );
				if( $code == $pak ){
					$modelUser->edit( $user->userId, array( 'status' => 1 ) );
					$messenger->noteSuccess( $words->msgSuccess );
					$this->restart( './auth/login/'.$user->username );
				}
			}
			$messenger->noteError( $words->msgInvalidCode );
		}
	}

/*	public function login(){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$messenger	= $this->env->getMessenger();
		$words		= $this->env->getLanguage()->getWords( 'auth' );
		
		if( $session->has( 'userId' ) )
			return $this->redirect( 'auth', 'loginInside' );

		$username	= $request->get( 'username' );
		$password	= $request->get( 'password' );
		if( $request->has( 'login' ) ) {
			$conditions	= array(
				'username'	=> $username,
				'password'	=> md5( $password )
			);
			$model	= new Model_User( $this->env );
			$result	= $model->getAll( $conditions );
			if( count( $result ) == 1 ) {
				$user	= array_shift( $result );
				$message	= $words['login']['msgSuccess'];
				$messenger->noteSuccess( $message );
				$session->set( 'userId', $user->userId );
				$session->set( 'roleId', $user->roleId );
				$this->restart( './' );
			}
			else {
				$message	= $words['login']['msgErrorInvalidUser'];
				$messenger->noteError( $message );
			}
		}
		$this->addData( 'data', array( 'username' => $username ) );
	}*/
	public function login( $username = NULL ){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$messenger	= $this->env->getMessenger();
		$words		= $this->getWords( 'login' );
		
		if( $session->has( 'userId' ) )
			return $this->redirect( 'auth', 'loginInside' );
		
		if( $request->has( 'doLogin' ) ) {
			$username	= $request->get( 'login_username' );
			$password	= $request->get( 'login_password' );
			if( !trim( $username ) )
				$messenger->noteError( $words->msgNoUsername );
			if( !trim( $password ) )
				$messenger->noteError( $words->msgNoPassword );

			if( !$messenger->gotError() ){
				$modelUser	= new Model_User( $this->env );
				$modelRole	= new Model_Role( $this->env );
				$user		= $modelUser->getByIndex( 'username', $username );
				if( !$user )
					$messenger->noteError( $words->msgInvalidUser );
				else{
					$role	= $modelRole->get( $user->roleId );
					if( !$role->access )
						$messenger->noteError( $words->msgInvalidRole );
					else if( $user->password !== md5( $password ) )
						$messenger->noteError( $words->msgInvalidPassword );
					else if( $user->status == 0 )
						$messenger->noteError( $words->msgUserUnconfirmed );
					else if( $user->status == -1 )
						$messenger->noteError( $words->msgUserLocked );
					else if( $user->status == -2 )
						$messenger->noteError( $words->msgUserDisabled );

					if( !$messenger->gotError() ){
						$modelUser->edit( $user->userId, array( 'loggedAt' => time() ) );
						$messenger->noteSuccess( $words->msgSuccess );
						$session->set( 'userId', $user->userId );
						$session->set( 'roleId', $user->roleId );
						if( $request->get( 'from' ) )
							$this->restart( './'.$request->get(' from' ) );
						$this->restart( './' );
					}
				}
			}
		}
		$this->addData( 'data', array( 'login_username' => $username ) );
	}

	public function logout( $redirectController = NULL, $redirectAction = NULL ){
		$session	= $this->env->getSession();
		$words		= $this->env->getLanguage()->getWords( 'auth' );
		$message	= $words['logout']['msgSuccess'];
		if( $session->remove( 'userId' ) ){
			$this->env->getMessenger()->noteSuccess( $message );
			$session->remove( 'userId' );
			$session->remove( 'roleId' );
		}
		$redirectTo	= '';
		if( $redirectController && $redirectAction )
			$redirectTo	= $redirectController.'/'.$redirectAction;
		else if( $redirectController )
			$redirectTo	= $redirectController;
		$this->restart( './'.$redirectTo );
	}

	public function loginInside(){}

	public function password(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= $this->getWords( 'password' );

		if( $request->has( 'password_email' ) ){
			if( $request->get( 'password_email' ) ){
				$modelUser	= new Model_User( $this->env );
				$user		= $modelUser->getByIndex( 'email', $request->get( 'password_email' ) );
				if( $user ){
					$randomizer	= new Alg_Randomizer();
					$randomizer->configure( TRUE, TRUE, TRUE, FALSE, 0 );
					$password	= $randomizer->get( 8 );
					$modelUser->edit( $user->userId, array( 'password' => md5( $password ) ) );
					$messenger->noteNotice( 'Neues Passwort: '.$password." <small><em>(Diese Meldung kommt nicht im Live-Betrieb.)</em></small>" );	//  @todo: remove before going live
					$messenger->noteSuccess( $words->msgSuccess );
					$this->restart( './auth/login' );
				}
				$messenger->noteError( $words->msgInvalidEmail );
			}
			$messenger->noteError( $words->msgNoEmail );
		}
	}

	public function register(){
		$config		= $this->env->getConfig();
#		print_m( $config->getAll() );
#		remark( CMC_VERSION );
#		die;
	
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$messenger	= $this->env->getMessenger();
		$words			= $this->getWords( 'register' );

		$modelUser	= new Model_User( $this->env );
		$modelRole	= new Model_Role( $this->env );

		$roleDefaultId	= $modelRole->getByIndex( 'register', 128, 'roleId' );
		$rolesAllowed	= array();
		foreach( $modelRole->getAllByIndex( 'register', array( 64, 128 ) ) as $role )
				$rolesAllowed[]	= $role->roleId;
				
		$input		= $request->getAllFromSource( 'post' );
		
		$nameMinLength	= $config->get( 'module.users.name.length.min' );
		$nameMaxLength	= $config->get( 'module.users.name.length.max' );
		$nameRegExp		= $config->get( 'module.users.name.preg' );
		$pwdMinLength	= $config->get( 'module.users.password.length.min' );
		$needsEmail		= $config->get( 'module.users.email.mandatory' );
		$needsFirstname	= $config->get( 'module.users.firstname.mandatory' );
		$needsSurname	= $config->get( 'module.users.surname.mandatory' );
		$needsTac		= $config->get( 'module.users.tac.mandatory' );
		$status			= (int) $config->get( 'module.users.status.register' );
		$passwordSalt	= trim( $config->get( 'module.users.password.salt' ) );						//  string to salt password with

		$roleId		= $request->has( 'roleId' ) ? $input->get( 'roleId' ) : $roleDefaultId;			//  use default register role if none given
		$username	= $input->get( 'username' );
		$password	= $input->get( 'password' );
		$email		= $input->get( 'email' );
	
		$errors	= $messenger->gotError();
		if( $request->get( 'saveUser' ) ){
			if( !in_array( $roleId, $rolesAllowed ) )
				$messenger->noteError( $words->msgRoleInvalid );
			if( empty( $username ) )
				$messenger->noteError( $words->msgNoUsername );
			else if( $modelUser->countByIndex( 'username', $username ) )
				$messenger->noteError( $words->msgUsernameExisting, $username );
			else if( $nameRegExp )
				if( !Alg_Validation_Predicates::isPreg( $username, $nameRegExp ) )
					$messenger->noteError( $words->msgUsernameInvalid, $username, $nameRegExp );
			if( empty( $password ) )
				$messenger->noteError( $words->msgNoPassword );
			else if( $pwdMinLength && strlen( $password ) < $pwdMinLength )
				$messenger->noteError( $words->msgPasswordTooShort, $pwdMinLength );
			if( $needsEmail && empty( $email ) )
				$messenger->noteError( $words->msgNoEmail);
			else if( !empty( $email ) && $modelUser->countByIndex( 'email', $email ) )
				$messenger->noteError( $words->msgEmailExisting, $email );
			if( $needsFirstname && empty( $input['firstname'] ) )
				$messenger->noteError( $words->msgNoFirstname );
			if( $needsSurname && empty( $input['surname'] ) )
				$messenger->noteError( $words->msgNoSurname );
			if( $needsTac &&  empty( $input['accept_tac'] ) )
				$messenger->noteError( $words->msgTermsNotAccepted  );

			if( $messenger->gotError() - $errors == 0 ){
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
				$messenger->noteSuccess( $words->msgSuccess );
				
				if( !$status ){
					$pak		= md5( 'pak:'.$userId.'/'.$username.'&'.$passwordSalt );
					$data	= $input->getAll();
					$data['pak']		= $pak;
					$data['password']	= $password;
					$mail		= new Mail_Auth_Register( $this->env, $data );
					$mail->sendToAddress( $email );
					$messenger->noteNotice( $words->msgNoticeConfirm );
					$this->restart( './auth/confirm' );
				}
				$this->restart( './auth/login' );
			}
		}
		foreach( $input as $key => $value )
			$input[$key]	= htmlentities( $value, ENT_COMPAT, 'UTF-8' );
		$this->addData( 'register', $input );
	}
}
?>
