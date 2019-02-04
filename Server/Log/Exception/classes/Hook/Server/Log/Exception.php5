<?php
class Hook_Server_Log_Exception extends CMF_Hydrogen_Hook{

	static public function onEnvLogException( $env, $context, $module, $data = array() ){
		if( is_object( $data ) && $data instanceof Exception )
			$data	= array( 'exception' => $data );
		if( !isset( $data['exception'] ) )
			throw new InvalidArgumentException( 'Missing exception in given hook call data' );
		if( !is_object( $data['exception'] ) )
			throw new InvalidArgumentException( 'Given exception is not an object' );
		if( !( $data['exception'] instanceof Exception ) )
			throw new InvalidArgumentException( 'Given exception object is not an exception instance' );

		$logic			= $env->getLogic()->get( 'logException');
		$moduleConfig	= $env->getConfig()->getAll( 'module.server_log_exception.', TRUE );
		$content		= $logic->collectData( $data['exception'] );

		$logic->saveCollectedDataToLogFile( $content );
		$logic->sendCollectedDataAsMail( $content );

		return TRUE;															//  mark hook as handled
	}

}
