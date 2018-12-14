<?php
class Hook_System_Exception extends CMF_Hydrogen_Hook{

	static public function onAppException( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$env->getCaptain()->callHook( 'Env', 'logException', $context, $data );	//  @todo replace $data by (array) payload after migration

		$payload	= (object) $data;
		if( !property_exists( $payload, 'exception' ) )
			throw new \RangeException( 'No exception given' );
		if( !( $payload->exception instanceof \Exception ) )
			throw new \RangeException( 'Given exception is not an exception instance' );

		if( $env->getRequest()->get( 'controller' ) === 'system/exception' )
			return FALSE;
		if( !$env->getConfig()->get( 'module.server_system_exception.active' ) )
			return FALSE;

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
				$env->getSession()->set( 'exceptionRequest', $env->getRequest() );
				$env->getSession()->set( 'exceptionUrl', $env->getRequest()->getUrl() );
				static::restart( $env, 'system/exception', 500 );
			case 'dev':
			case 'strict':
			default:
				UI_HTML_Exception_Page::display( $payload->exception );
				exit;
		}
	}
}
