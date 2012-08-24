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
 *	@extends		Controller_Abstract
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Syslog.php5 3022 2012-06-26 20:08:10Z christian.wuerker $
 */
class Controller_Syslog extends Controller_Abstract {

	/**	@var		Environment		$env		Environment instance */
	protected $env;

	public function index(){
		try{
			return $this->getLinesFromLog();
		}
		catch( Exception $e ){
			$this->logException( $e );
			return -105;
		}
	}

	public function get( $nr ){
		try{
			return $this->getLineFromLog( $nr );
		}
		catch( Exception $e ){
			$this->logException( $e );
			return -105;
		}
	}

	public function getExceptionView( $nr ){
		try{
			$line	= $this->getLineFromLog( $nr );													//  get line from log file
			$parts	= preg_split( '/:/', $line, 2 );												//  extract line parts
			$view	= base64_decode( trim( array_pop( $parts ) ) );									//  restore exception view
			return $view;																			//  return extracted HTML content
		}
		catch( Exception $e ){
			$this->logException( $e );
			return -105;
		}
	}

	public function getExceptionPage( $nr ){
		try{
			$view	= $this->getExceptionView( $nr );												//  get rendered exception view
			$page	= new UI_HTML_Exception_Page();													//  create new HTML page
			$html	= $page->wrapExceptionView( $view );											//  wrap HTML page around exception view
			return $html;																			//  return HTML page
		}
		catch( Exception $e ){
			$this->logException( $e );
			return -105;
		}
	}

	public function sendMailToDeveloper( $fromAddress, $fromName = NULL ){
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
			$mail	= new Mail_Syslog( $this->env, array(
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
	}

	public function remove( $nr ){
		$lines	= $this->getLinesFromLog();
		if( isset( $lines[$nr] ) ){
			unset( $lines[$nr] );
			$fileName	= $this->env->getConfig()->get( 'log.exception' );
			File_Writer::saveArray( $fileName, $lines );
			return 1;
		}
		return -1;
	}

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
		return File_Reader::loadArray( $fileName );
	}

	public function logTestException( $message, $code = 0 ){
		$exception	= new Exception( $message, $code );
		$this->logException( $exception );
		return 1;
	}
}
?>
