<?php
class Controller_Auth extends CMF_Hydrogen_Controller {

	public function login(){
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
}
?>
