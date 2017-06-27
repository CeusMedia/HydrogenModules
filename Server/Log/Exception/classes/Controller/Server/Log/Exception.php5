<?php
/**
 *	Server Log Exception Controller.
 *	@category		CeusMedia.Hydrogen.Module
 *	@package		Server.Log.Exception
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2017 Ceus Media {@link https://ceusmedia.de/}
 */
/**
 *	Server Log Exception Controller.
 *	@category		CeusMedia.Hydrogen.Module
 *	@package		Server.Log.Exception
 *	@extends		CMF_Hydrogen_Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2017 Ceus Media {@link https://ceusmedia.de/}
 */
class Controller_Server_Log_Exception extends CMF_Hydrogen_Controller{

	static public function __onEnvLogException( $env, $context, $module, $data = array() ){
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
			if( trim( $moduleConfig->get( 'file.path' ) ) ){
				$pathLogs		= $env->getConfig()->get( 'path.logs' );
				$filePath		= $pathLogs.$moduleConfig->get( 'file.name' );
				try{
					$content	= @serialize( $exception );
				}
				catch( Exception $_e ){
					$content	= serialize( (object) array(
						'message'		=> $exception->getMessage(),
						'code'			=> $exception->getCode(),
						'class'			=> get_class( $exception ),
						'file'			=> $exception->getFile(),
						'line'			=> $exception->getLine(),
						'trace'			=> $exception->getTraceAsString(),
	//					'traceAsString'	=> $exception->getTraceAsString(),
	//					'traceAsHtml'	=> UI_HTML_Exception_Trace::render( $exception ),
					) );
				}
				$msg	= time().":".base64_encode( $content );
				error_log( $msg.PHP_EOL, 3, $filePath );
			}
		}

		if( $moduleConfig->get( 'mail.active' ) ){
			if( trim( $moduleConfig->get( 'mail.receiver' ) ) ){
				$language		= $env->getLanguage()->getLanguage();
				$logicMail		= new Logic_Mail( $env );
				$mail			= new Mail_Log_Exception( $env, $data );
				$receivers		= preg_split( '/(,|;)/', $moduleConfig->get( 'mail.receiver' ) );
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

	static public function logException( $env, $exception ){
		$env->getCaptain()->callHook( 'Env', 'logException', $this, array( 'exception' => $exception ) );
	}

	public function logTestException( $message, $code = 0 ){
		$exception	= new Exception( $message, $code );
//		$this->callHook( 'Server:System', 'logException', $this, $exception );
//		self::handleException( $this->env, $exception );
		self::logException( $this->env, $exception );
		$this->restart( NULL, TRUE );
	}
}
