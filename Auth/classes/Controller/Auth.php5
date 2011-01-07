<?php
class Controller_Auth extends CMF_Hydrogen_Controller {

	public function login(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= $this->env->getLanguage()->getWords( 'auth' );
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
				$this->env->getSession()->set( 'userId', $user->userId );
				$this->env->getSession()->set( 'roleId', $user->roleId );
				$this->restart( './' );
			}
			else {
				$message	= $words['login']['msgErrorInvalidUser'];
				$messenger->noteError( $message );
			}
		}
		$this->addData( 'data', array( 'username' => $username ) );
	}

	public function logout(){
		$words		= $this->env->getLanguage()->getWords( 'auth' );
		$message	= $words['logout']['msgSuccess'];
		if( $this->env->getSession()->remove( 'userId' ) ){
			$this->env->getMessenger()->noteSuccess( $message );
			$this->env->getSession()->remove( 'userId' );
			$this->env->getSession()->remove( 'roleId' );
		}
		$this->restart( './' );
	}
}
?>
