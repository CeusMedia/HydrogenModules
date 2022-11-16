<?php

use CeusMedia\Common\UI\HTML\Exception\Page as HtmlExceptionPage;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Console as ConsoleEnvironment;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_System_Exception extends Hook
{
	static public function onAppException( Environment $env, object $context, $module, array & $payload )
	{
		$env->getCaptain()->callHook( 'Env', 'logException', $context, $payload );

		if( !isset( $payload['exception'] ) )
			throw new RangeException( 'No exception given' );
		if( !( $payload['exception'] instanceof Exception ) )
			throw new RangeException( 'Given exception is not an exception instance' );

		if( $env->getRequest()->get( '__controller' ) === 'system/exception' )
			return FALSE;
		if( !$env->getConfig()->get( 'module.server_system_exception.active' ) )
			return FALSE;

		$e	= $payload['exception'];
		if( $env instanceof WebEnvironment ){
			$requestUrl	= $env->getRequest()->getUrl();
			if( $env->getRequest()->isAjax() ){
				$env->getResponse()->setStatus( 500 );
				$env->getResponse()->setBody( json_encode( array(
					'status'	=> 'exception',
					'message'	=> $e->getMessage(),
					'code'		=> $e->getCode(),
					'file'		=> $e->getFile(),
					'line'		=> $e->getLine(),
					'trace'		=> $e->getTraceAsString(),
				) ) );
				$env->getResponse()->send();
				exit;
			}
		}
		else if( $env instanceof ConsoleEnvironment ){
			global $argv;
			$requestUrl	= join( ' ', $argv );
		}

		$options	= $env->getConfig()->getAll( 'module.server_system_exception.', TRUE );
		$mode		= $options->get( 'mode' ) ? $options->get( 'mode' ) : 'info';
		switch( $mode ){
			case 'info':
				$env->getSession()->set( 'exception', serialize( (object) array(
					'message'	=> $e->getMessage(),
					'code'		=> $e->getCode(),
					'file'		=> $e->getFile(),
					'line'		=> $e->getLine(),
					'trace'		=> $e->getTraceAsString(),
				) ) );
				$env->getSession()->set( 'exceptionRequest', $env->getRequest() );
				$env->getSession()->set( 'exceptionUrl', $requestUrl );
				static::restart( $env, 'system/exception', 500 );
			case 'dev':
			case 'strict':
			default:
				HtmlExceptionPage::display( $e );
				exit;
		}
	}
}
