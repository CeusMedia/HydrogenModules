<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Server_Log_Exception extends Hook
{
	/**
	 *	@return		bool
	 *	@throws		ReflectionException
	 */
	public function onEnvLogException(): bool
	{
		$data	= $this->getPayload();
		if( is_object( $data ) && $data instanceof Throwable )
			$data	= ['exception' => $data];

		if( !isset( $data['exception'] ) )
			throw new InvalidArgumentException( 'Missing exception in given hook call data' );
		if( !is_object( $data['exception'] ) )
			throw new InvalidArgumentException( 'Given exception is not an object' );
		if( !( $data['exception'] instanceof Exception ) )
			throw new InvalidArgumentException( 'Given exception object is not an exception instance' );

		$logic			= $this->env->getLogic()->get( 'logException');
		$moduleConfig	= $this->env->getConfig()->getAll( 'module.server_log_exception.', TRUE );
		$content		= $logic->collectData( $data['exception'] );

		$logic->saveCollectedDataToLogFile( $content );
//		$logic->sendCollectedDataAsMail( $content );
		$logic->sendExceptionAsMail( $data['exception'] );

		return FALSE;															//  mark hook as unhandled
	}
}
