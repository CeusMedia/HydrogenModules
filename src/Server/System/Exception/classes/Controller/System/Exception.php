<?php

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\Common\Net\HTTP\Status as HttpStatus;

class Controller_System_Exception extends Controller
{
	public function index()
	{
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		if( $session->has( 'exception' ) ){
			$exception	= unserialize( $session->get( 'exception' ) );
			if( $session->has( 'exceptionRequest' ) ){
				$this->addData( 'exceptionRequest', $session->get( 'exceptionRequest' ) );
			}
			if( $session->has( 'exceptionUrl' ) ){
				$this->addData( 'exceptionUrl', $session->get( 'exceptionUrl' ) );
			}
			if( isset( $exception->code ) && is_int( $exception->code ) ){
				if( HttpStatus::isCode( $exception->code ) ){
					HttpStatus::sendHeader( $exception->code );					//  send HTTP status code header
					$this->env->getResponse()->setStatus( $exception->code );			//  indicate HTTP status 500 - internal server error
				}
			}
			$this->addData( 'exception', $exception );
//			$session->remove( 'exception' );
		}
		else{
			if( !$request->get( '__controller' ) == 'system' ){
				if( !$request->get( '__action' ) == 'exception' ){
					$this->restart( NULL, FALSE, 400 );
				}
			}
//			$this->restart();
		}
	}

	public function reset( $path = NULL )
	{
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();
		if( $session->has( 'exception' ) ){
			$session->remove( 'exception' );
			$session->remove( 'exceptionRequest' );
			$session->remove( 'exceptionUrl' );
		}
		$this->restart( $path );
	}

	public function test( $useHook = FALSE )
	{
		if( $useHook ){
			try{
				throw new Exception( 'This is a test' );
			}
			catch( Exception $e ){
				$payload	= ['exception' => $e];
				$this->env->getCaptain()->callHook( 'App', 'onException', $this, $payload );
				throw new Exception( $e->getMessage(), $e->getCode(), $e );
			}
		}
		else{
			throw new Exception( 'This is a test' );
		}
	}
}
