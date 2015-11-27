<?php
/**
 *	System Log Controller.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Syslog.php5 3022 2012-06-26 20:08:10Z christian.wuerker $
 */
/**
 *	System Log Controller.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@extends		CMF_Hydrogen_Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Syslog.php5 3022 2012-06-26 20:08:10Z christian.wuerker $
 */
class Controller_System_Log extends CMF_Hydrogen_Controller{

	/**	@var		Environment		$env		Environment instance */
	protected $env;

	static public function ___onLogException( $env, $context, $module, $data = array() ){
		if( !isset( $data['exception'] ) )
			throw new InvalidArgumentException( 'Missing exception in given hook call data' );

		$exception	= $data['exception'];
		$config		= $env->getConfig()->getAll( 'module.server_system_log.', TRUE );
		if( $config->get( 'file.active' ) && $config->get( 'file.name' ) ){
			try{
				$serial		= base64_encode( serialize( $exception ) );
			}
			catch( Exception $e ){
				$serial		= base64_encode( serialize( (object) array(
					'message'	=> $exception->getMessage(),
					'code'		=> $exception->getCode(),
					'file'		=> $exception->getFile(),
					'line'		=> $exception->getLine(),
					'trace'		=> $exception->getTraceAsString(),
				) ) );
			}
			error_log( time().":".$serial."\n", 3, $config->get( 'file.name' ) );
		}
		if( $config->get( 'email.active' ) && $config->get( 'email.receivers' ) ){
			foreach( explode( ",", $config->get( 'email.receivers' ) ) as $receiver ){
				if( strlen( trim( $receiver ) ) ){
					$user	= array( 'email' => trim( $receiver ) );
					$mail	= new Mail_System_Log_Exception( $env, array( 'exception' => $exception ) );
					try{
						$mail->sendTo( (object) $user );
					}
					catch( Exception $e ){}
				}
			}
		}
	}

/*	public function index(){
	}*/

/*	public function view( $nr ){
	}*/

/*	public function sendMailToDeveloper( $fromAddress, $fromName = NULL ){
		$subject	= trim( $this->env->getRequest()->get( 'subject' ) );
		$body		= trim( $this->env->getRequest()->get( 'body' ) );
		$receiver	= $this->env->config->get( 'app.email.developer' );								//  @todo	replace by module email address (line below)
#		$receiver	= $this->env->config->get( 'module.server_syslog.email.developer' );
		$prefix		= trim( $this->env->config->get( 'module.resource_mail.subject.prefix' ) );

		if( !trim( $fromAddress ) )
			return -1;
		if( !trim( $fromName ) )
			return -2;
		if( !trim( $subject ) )
			return -3;
		if( !trim( $body ) )
			return -4;
		if( !trim( $receiver ) )
			return -5;
		try
		{
			$mail	= new Mail_System_Log( $this->env, array(
				'body'		=> $body,
				'prefix'	=> $prefix,
				'subject'	=> $subject,
				'sender'	=> $fromName ? $fromName.' <'.$fromAddress.'>' : $fromAddress,
			) );
			$mail->sendToAddress( $receiver );
			return 1;
		}
		catch( Exception $e ){
			return $e->getMessage();
		}
	}*/

/*	public function remove( $nr ){
		$lines	= $this->getLinesFromLog();
		if( isset( $lines[$nr] ) ){
			unset( $lines[$nr] );
			$fileName	= $this->env->getConfig()->get( 'log.exception' );
			FS_File_Writer::saveArray( $fileName, $lines );
			return 1;
		}
		return -1;
	}*/

	/**
	 *	Returns a request line from exception log.
	 *	@access		protected
	 *	@param		integer		$nr			Line number in log file
	 *	@return		string		Line content with timestamp and encoded exception view
	 */
	protected function getLineFromLog( $nr ){
		$lines	= $this->getLinesFromLog();
		$line	= isset( $lines[$nr] ) ? trim( $lines[$nr] ) : '';
		if( !$line )
			throw new InvalidArgumentException( 'Line #'.$nr.' not existing' );
		return $line;
	}

	/**
	 *	Returns all lines from exception log.
	 *	@access		protected
	 *	@return		array		List if lines with timestamp and encoded exception view
	 */
	protected function getLinesFromLog(){
		$fileName	= $this->env->getConfig()->get( 'log.exception' );
		if( !file_exists( $fileName ) )
			return array();
#			throw new RuntimeException( 'Log not existing' );
		return FS_File_Reader::loadArray( $fileName );
	}

	public function logTestException( $message, $code = 0 ){
		$exception	= new Exception( $message, $code );
		$this->logException( $exception );
		return 1;
	}
}
?>
