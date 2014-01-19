<?php
/**
 *	@todo		localize
 *	@todo		integrate validation from Controller_Admin_User::edit
 */
class Controller_Manage_My_User extends CMF_Hydrogen_Controller{

	public function index(){
		$config		= $this->env->getConfig();
		$session	= $this->env->getSession();
		$messenger	= $this->env->getMessenger();
		$userId		= $session->get( 'userId' );
		$roleId		= $session->get( 'roleId' );
		$modelUser	= new Model_User( $this->env );
		if( !$userId ){
			$messenger->noteFailure( 'Nicht eingeloggt. Zugriff verweigert.' );
			$this->restart( './' );
		}
		$user		= $modelUser->get( $userId );
		if( !$user ){
			$messenger->noteFailure( 'Zugriff verweigert.' );
			$this->restart( './manage/my' );
		}
		if( $config->get( 'module.roles' ) ){
			$modelRole	= new Model_Role( $this->env );
			$user->role	= $modelRole->get( $user->roleId );
		}
		if( $config->get( 'module.companies' ) ){
			$modelCompany	= new Model_Company( $this->env );
			$user->company	= $modelCompany->get( $user->companyId );
		}
		$this->addData( 'user', $user );
		$this->addData( 'pwdMinLength', (int) $config->get( 'module.resource_users.password.length.min' ) );
		$this->addData( 'pwdMinStrength', (int) $config->get( 'module.resource_users.password.strength.min' ) );
		$this->addData( 'mandatoryEmail', (int) $config->get( 'module.resource_users.email.mandatory' ) );
		$this->addData( 'mandatoryFirstname', (int) $config->get( 'module.resource_users.firstname.mandatory' ) );
		$this->addData( 'mandatorySurname', (int) $config->get( 'module.resource_users.surname.mandatory' ) );
	}

	/**
	 *	@todo		integrate validation from Controller_Admin_User::edit
	 */
	public function edit(){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'index' );
		$userId		= $session->get( 'userId' );
		$modelUser	= new Model_User( $this->env );

		if( !$userId ){
			$messenger->noteFailure( 'Nicht eingeloggt. Zugriff verweigert.' );
			$this->restart( './' );
		}
		$user		= $modelUser->get( $userId );
		if( !$user ){
			$messenger->noteFailure( 'Zugriff verweigert.' );
			$this->restart( './manage/my' );
		}

		$data		= $request->getAllFromSource( 'POST' )->getAll();
		
		$deniedKeys	= array( 'password', 'createdAt', 'modifiedAt', 'roleId', 'companyId', 'saveUser' );
		foreach( $deniedKeys as $deniedKey )
			unset( $data[$deniedKey] );

		$indices	= array(
			'username'	=> $data['username'],
			'userId'	=> '!='.$userId,
			'status'	=> '>=-1',
		);
		if( empty( $data['username'] ) )
			$messenger->noteError( $words->msgNoUsername );
		else if( $modelUser->getByIndices( $indices ) )
			$messenger->noteError( $words->msgUsernameExisting, $data['username'] );

		$indices	= array(
			'email'		=> $data['email'],
			'userId'	=> '!='.$userId,
			'status'	=> '>=-1',
		);
		
		$options		= $this->env->getConfig()->getAll( 'module.resource_users.', TRUE );
		$needsEmail		= (int) $options->get( 'email.mandatory' );
		$needsFirstname	= (int) $options->get( 'firstname.mandatory' );
		$needsSurname	= (int) $options->get( 'surname.mandatory' );
		
		if( $needsEmail && empty( $data['email'] ) )
			$messenger->noteError( $words->msgNoEmail );
		else if( $modelUser->getByIndices( $indices ) )
			$messenger->noteError( $words->msgEmailExisting, $data['email'] );

		if( $needsFirstname && empty( $data['firstname'] ) )
			$messenger->noteError( $words->msgNoFirstname );
		if( $needsSurname && empty( $data['surname'] ) )
			$messenger->noteError( $words->msgNoSurname );
		
		/*		if( empty( $data['postcode'] ) )
			$messenger->noteError( $words->msgNoPostcode );
		if( empty( $data['city'] ) )
			$messenger->noteError( $words->msgNoCity );
		if( empty( $data['street'] ) )
			$messenger->noteError( $words->msgNoStreet );
		if( empty( $data['number'] ) )
			$messenger->noteError( $words->msgNoNumber );*/
		
		if( !trim( $request->get( 'password' ) ) )
			$messenger->noteError( $words->msgNoPassword );
		else if( $user->password !== md5( $request->get( 'password' ) ) )
			$messenger->noteError( $words->msgPasswordMismatch );

		if( !$messenger->gotError() ){
			$modelUser->edit( $userId, $data );
			$messenger->noteSuccess( $words->msgSuccess );
		};
		$this->restart( './manage/my/user' );
	}

	/**
	 *	@todo		integrate validation from Controller_Admin_User::edit
	 */
	public function password(){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'password' );
		$userId		= $session->get( 'userId' );
		$modelUser	= new Model_User( $this->env );

		if( !$userId ){
			$messenger->noteError( 'Nicht eingeloggt. Zugriff verweigert.' );
			$this->restart( './' );
		}
		$user		= $modelUser->get( $userId );
		if( !$user ){
			$messenger->noteError( 'Zugriff verweigert.' );
			$this->restart( './manage/my' );
		}

		$options		= $this->env->getConfig()->getAll( 'module.resource_users.', TRUE );
		$pwdMinLength	= (int) $options->get( 'password.length.min' );
		$pwdMinStrength	= (int) $options->get( 'password.strength.min' );
		$passwordSalt	= trim( $options->get( 'password.salt' ) );						//  string to salt password with
		
		$data = $request->getAllFromSource( 'post' );
		if( empty( $data['passwordOld'] ) )
			$messenger->noteError( $words->msgNoPasswordOld );
		else if( md5( $data['passwordOld'] ) !== $user->password )
			$messenger->noteError( $words->msgPasswordMismatch );
		if( empty( $data['passwordNew'] ) )
			$messenger->noteError( $words->msgNoPasswordNew );
		else if( $pwdMinLength && strlen( $data['passwordNew'] ) < $pwdMinLength )
			$messenger->noteError( $words->msgPasswordTooShort, $pwdMinLength );

		if( !$messenger->gotError() ){
			$modelUser->edit( $userId, array( 'password' => md5( $data['passwordNew'] ) ) );
			$messenger->noteSuccess( $words->msgSuccess );
		}
		$this->restart( './manage/my/user' );
	}
}
?>