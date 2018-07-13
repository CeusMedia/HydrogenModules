<?php
class Hook_System_Exception extends CMF_Hydrogen_Hook{

	static public function onAppException( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		if( !isset( $data['exception'] ) )
			throw new Exception( 'No exception data given' );
		if( !( $data['exception'] instanceof Exception ) )
			throw new Exception( 'Given exception data is not an exception object' );
		if( $env->getRequest()->get( 'controller' ) === 'system/exception' )
			return FALSE;
		if( !$env->getConfig()->get( 'module.server_system_exception.enabled' ) )
			return FALSE;
//UI_HTML_Exception_Page::display( $data['exception'] );die;
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
}
