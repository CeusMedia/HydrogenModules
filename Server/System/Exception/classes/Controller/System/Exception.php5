<?php
class Controller_System_Exception extends CMF_Hydrogen_Controller{

	public function index(){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$exception	= unserialize( $session->get( 'exception' ) );
		if( $session->has( 'exceptionRequest' ) ){
			$this->addData( 'exceptionRequest', $session->get( 'exceptionRequest' ) );
		}
		if( $session->has( 'exceptionUrl' ) ){
			$this->addData( 'exceptionUrl', $session->get( 'exceptionUrl' ) );
		}
		if( $session->has( 'exception' ) ){
			if( isset( $exception->code ) ){
				if( Net_HTTP_Status::isCode( $exception->code ) ){
					Net_HTTP_Status::sendHeader( $exception->code );					//  send HTTP status code header
					$this->env->getResponse()->setStatus( $exception->code );			//  indicate HTTP status 500 - internal server error
				}
			}
			$this->addData( 'exception', $exception );
//			$session->remove( 'exception' );
		}
		else{
			if( !$request->get( 'controller' ) == 'system' )
 				if( !$request->get( 'action' ) == 'exception' )
					$this->restart( NULL, FALSE, 400 );
		}
	}
}
