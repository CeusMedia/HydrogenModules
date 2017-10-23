<?php
class Controller_System_Exception extends CMF_Hydrogen_Controller{

	static public function ___onAppException( CMF_Hydrogen_Environment_Abstract $env, $context, $module, $data = array() ){
		$exception	= $data['exception'];
		$env->getSession()->set( 'exception', serialize( (object) array(
			'message'	=> $exception->getMessage(),
 			'code'		=> $exception->getCode(),
			'file'		=> $exception->getFile(),
			'line'		=> $exception->getLine(),
			'trace'		=> $exception->getTraceAsString(),
		) ) );
		header( 'Location: '.$env->url.'system/exception' );
		exit;
	}

	public function index(){
		$session	= $this->env->getSession();
		if( $session->has( 'exception' ) ){
			$this->addData( 'exception', unserialize( $session->get( 'exception' ) ) );
//			$session->remove( 'exception' );
		}
		else
			$this->restart( NULL, FALSE );
	}
}
