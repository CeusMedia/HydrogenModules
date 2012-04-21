<?php
class Controller_Auth extends CMF_Hydrogen_Controller {

	public function confirm(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= $this->getWords( 'confirm' );

		if( $request->has( 'confirm_code' ) ){
			$modelUser	= new Model_User( $this->env );
			$users		= $modelUser->getAllByIndex( 'status', 0 );
			foreach( $users as $user ){
				$pak	= md5( 'pak_'.$user->userId.'_'.$user->username );
				if( $request->get( 'confirm_code' ) == $pak ){
					$modelUser->edit( $user->userId, array( 'status' => 1 ) );
					$messenger->noteSuccess( $words->msgSuccess );
					$this->restart( './auth/login' );
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
	public function login(){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$messenger	= $this->env->getMessenger();
		$words		= $this->getWords( 'login' );
		
		if( $session->has( 'userId' ) )
			return $this->redirect( 'auth', 'loginInside' );

		$username	= $request->get( 'login_username' );
		$password	= $request->get( 'login_password' );
		if( $request->has( 'doLogin' ) ) {
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
					else if( $user->status == 0 ){
						$messenger->noteError( $words->msgUserUnconfirmed );
						$pak	= md5( 'pak_'.$user->userId.'_'.$user->username );
//						$messenger->noteNotice( 'Bestätigungs-Code: '.$pak.' <small><em>(Diese Meldung kommt nicht im Live-Betrieb.)</em></small>' );										//  @todo: remove before going live
					}
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
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$messenger	= $this->env->getMessenger();
	}
}
?>
