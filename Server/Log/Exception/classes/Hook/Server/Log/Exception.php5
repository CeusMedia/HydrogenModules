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

		$moduleConfig	= $env->getConfig()->getAll( 'module.server_log_exception.', TRUE );
		$exception		= $data['exception'];

		if( $moduleConfig->get( 'file.active' ) ){
			if( trim( $moduleConfig->get( 'file.name' ) ) ){
				$pathLogs		= $env->getConfig()->get( 'path.logs' );
				$filePathName	= $pathLogs.$moduleConfig->get( 'file.name' );
				try{
					@serialize( $exception );
					$content	= (object) array(
						'exception'		=> $exception,
					);
				}
				catch( Exception $_e ){
					$content	= (object) array(
						'message'		=> $exception->getMessage(),
						'code'			=> $exception->getCode(),
						'file'			=> $exception->getFile(),
						'line'			=> $exception->getLine(),
						'trace'			=> $exception->getTraceAsString(),
						'previous'		=> $exception->getPrevious(),
					);
				}
//				$content->traceAsString		= $exception->getTraceAsString();
//				$content->traceAsHtml		= UI_HTML_Exception_Trace::render( $exception );
				$content->request			= $env->getRequest()->getAll();
				$content->session			= $env->getSession()->getAll();
			//	$content->cookie			= $env->getCookie()->getAll();			// @todo activate for Hydrogen 0.8.6.5+
				$content->previous			= $exception->getPrevious();
				$content->class				= get_class( $exception );
				$content->classParents		= array();
				$content->classInterfaces	= array();
				$content->sqlState			= NULL;
				if( $content->class ){
					$content->classParents		= class_parents( $content->class );
					$content->classInterfaces	= class_implements( $content->class );
				}
				if( method_exists( $exception, 'getSQLSTATE' ) )
					$content->sqlState	= $exception->getSQLSTATE();

				$classes	= array_values( array( $content->class ) + $content->classParents );

				$content->resource		= NULL;
				if( in_array( 'Exception_IO', $classes ) )
					$content->resource		= $exception->getResource();
				$content->subject		= NULL;
				if( in_array( 'Exception_Logic', $classes ) )
					$content->subject		= $exception->getSubject();

				$msg	= time().":".base64_encode( serialize( $content ) );
				error_log( $msg.PHP_EOL, 3, $filePathName );
			}
		}

		if( $moduleConfig->get( 'mail.active' ) ){
			if( trim( $moduleConfig->get( 'mail.receivers' ) ) ){
				$language		= $env->getLanguage()->getLanguage();
				$logicMail		= Logic_Mail::getInstance( $env );
				$mail			= new Mail_Log_Exception( $env, $data );
				$receivers		= preg_split( '/(,|;)/', $moduleConfig->get( 'mail.receivers' ) );
				foreach( $receivers as $receiver ){
					if( trim( $receiver ) ){
						$receiver	= (object) array( 'email' => $receiver );
						$logicMail->handleMail( $mail, $receiver, $language );
					}
				}
			}
		}
		return TRUE;															//  mark hook as handled
	}
}
