<?php
class Controller_Index extends CMF_Hydrogen_Controller{
	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL, $arg5 = NULL, $arg6 = NULL ){
		if( $arg1 ){
			$response	= $this->env->getResponse();
			$response->setStatus( 404 );
#			$this->env->getMessenger()->noteError( '404' );
		}
		$userId         = $this->env->getSession()->get( 'userId' );
		$user		= NULL;
		if( $userId ){
			$model	= new Model_User( $this->env );
			$user	= $model->get( $userId );
#			$this->env->getMessenger()->noteNotice( 'Hallo '.$user->username.'!' );
		}
		$this->addData( 'user', $user );
	}
}
?>
