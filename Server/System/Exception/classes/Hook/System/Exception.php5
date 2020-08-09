<?php
class Hook_System_Exception extends CMF_Hydrogen_Hook{

	static public function onAppException( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$env->getCaptain()->callHook( 'Env', 'logException', $context, $data );	//  @todo replace $data by (array) payload after migration

		$payload	= (object) $data;
		if( !property_exists( $payload, 'exception' ) )
			throw new \RangeException( 'No exception given' );
		if( !( $payload->exception instanceof \Exception ) )
			throw new \RangeException( 'Given exception is not an exception instance' );

		if( $env->getRequest()->get( '__controller' ) === 'system/exception' )
			return FALSE;
		if( !$env->getConfig()->get( 'module.server_system_exception.active' ) )
			return FALSE;

		

		$e	= $payload->exception;
		if( $env instanceof CMF_Hydrogen_Environment_Web ){
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
		else if( $env instanceof CMF_Hydrogen_Environment_Console ){
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
				UI_HTML_Exception_Page::display( $e );
				exit;
		}
	}
}
