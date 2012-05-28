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
		$this->addData( 'pwdMinLength', (int) $config->get( 'module.users.password.length.min' ) );
		$this->addData( 'pwdMinStrength', (int) $config->get( 'module.users.password.strength.min' ) );
	}

	/**
	 *	@todo		integrate validation from Controller_Admin_User::edit
	 */
	public function edit(){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$messenger	= $this->env->getMessenger();
		$words		= $this->getWords( 'index' );
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
		if( empty( $data['email'] ) )
			$messenger->noteError( $words->msgNoEmail );
		else if( $modelUser->getByIndices( $indices ) )
			$messenger->noteError( $words->msgEmailExisting, $data['email'] );
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
		$words		= $this->getWords( 'password' );
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
		$data = $request->getAllFromSource( 'post' );
		if( empty( $data['passwordOld'] ) )
			$messenger->noteError( $words->msgNoPasswordOld );
		else if( md5( $data['passwordOld'] ) !== $user->password )
			$messenger->noteError( $words->msgPasswordMismatch );
		if( empty( $data['passwordNew'] ) )
			$messenger->noteError( $words->msgNoPasswordNew );
		if( !$messenger->gotError() ){
			$modelUser->edit( $userId, array( 'password' => md5( $data['passwordNew'] ) ) );
			$messenger->noteSuccess( $words->msgSuccess );
		}
		$this->restart( './manage/my/user' );
	}
}
?>