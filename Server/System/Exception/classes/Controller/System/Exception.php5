<?php
class Controller_System_Exception extends CMF_Hydrogen_Controller{

	/**
	 *	@todo migrate 'data' argument to 'payload'
	 */
	static public function ___onAppException( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$env->getCaptain()->callHook( 'Env', 'logException', $this, $data );	//  @todo replace $data by (array) payload after migration

		$payload	= (object) $data;
		if( !property_exists( $payload, 'exception' ) )
			throw new \RangeException( 'No exception given' );
		if( !( $payload->exception instanceof \Exception ) )
			throw new \RangeException( 'Given exception is not an exception instance' );

		$options	= $env->getConfig()->getAll( 'module.server_system_exception.', TRUE );
		$mode		= $options->get( 'mode' ) ? $options->get( 'mode' ) : 'info';
		switch( $mode ){
			case 'info':
				$env->getSession()->set( 'exception', serialize( (object) array(
					'message'	=> $payload->exception->getMessage(),
		 			'code'		=> $payload->exception->getCode(),
					'file'		=> $payload->exception->getFile(),
					'line'		=> $payload->exception->getLine(),
					'trace'		=> $payload->exception->getTraceAsString(),
				) ) );
				header( 'Location: '.$env->url.'system/exception' );
				exit;
			case 'dev':
			case 'strict':
			default:
				UI_HTML_Exception_Page::display( $payload->exception );
				exit;
		}
	}

	public function index(){
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		$exception	= unserialize( $session->get( 'exception' ) );
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
