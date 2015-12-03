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
	protected $moduleConfig;

	protected function __onInit(){
		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.server_system_log.', TRUE );

	}

	static public function ___onLogException( $env, $context, $module, $data = array() ){
		if( is_object( $data ) && $data instanceof Exception )
			$data	= array( 'exception' => $data );
		if( !isset( $data['exception'] ) )
			throw new InvalidArgumentException( 'Missing exception in given hook call data' );
		$exception	= $data['exception'];
		self::handleException( $env, $exception );
	}

	protected function count(){
		$fileName	= $this->moduleConfig->get( 'file.name' );
		if( !file_exists( $fileName ) )
			return array();
#			throw new RuntimeException( 'Log not existing' );
		$content	= trim( FS_File_Reader::load( $fileName ) );
		$lines		= explode( "\n", $content );
		return count( $lines );
	}

	/**
	 *	Returns a request line from exception log.
	 *	@access		protected
	 *	@param		integer		$nr			Line number in log file
	 *	@return		string		Line content with timestamp and encoded exception view
	 */
	protected function getLineFromLog( $nr, $descending = TRUE ){
		$lines	= $this->getLinesFromLog();
		$line	= isset( $lines[$nr] ) ? trim( $lines[$nr] ) : '';
		if( !$line )
			throw new InvalidArgumentException( 'Line #'.$nr.' not existing' );
		return $this->parseLine( $line );
	}

	/**
	 *	Returns all lines from exception log.
	 *	@access		protected
	 *	@return		array		List if lines with timestamp and encoded exception view
	 */
	protected function getLinesFromLog( $limit, $offset = 0, $descending = TRUE ){
		$fileName	= $this->moduleConfig->get( 'file.name' );
		if( !file_exists( $fileName ) )
			return array();
#			throw new RuntimeException( 'Log not existing' );
		$content	= trim( FS_File_Reader::load( $fileName ) );
		$lines		= explode( "\n", $content );
		$total		= count( $lines );
		if( $descending )
			$lines	= array_reverse( $lines );
		$lines		= array_slice( $lines, $offset, $limit );
		foreach( $lines as $nr => $line ){
			try{
				$id	= $descending ? $total - 1 - ( $nr + $offset ) : $nr + $offset;
				$lines[$nr]	= $this->parseLine( $line );
				$lines[$nr]->id	= $id;
			}
			catch( Exception $e ){
				unset( $lines[$nr] );
			}
		}
		return $lines;
	}

	static public function handleException( $env, $exception ){
		self::logException( $env, $exception );
		self::mailException( $env, $exception );
	}

	public function index( $page = 0, $limit = 10 ){
		$page	= preg_match( "/^[0-9]+$/", $page ) ? (int) $page : 0;
		$limit	= preg_match( "/^[0-9]+$/", $limit ) ? (int) $limit : 10;
		$this->env->getSession()->set( 'filter_server_system_page', $page );
		$this->env->getSession()->set( 'filter_server_system_limit', $limit );
		$lines	= $this->getLinesFromLog( $limit, $page * $limit );
		$this->addData( 'exceptions', $lines );
		$this->addData( 'total', $this->count() );
		$this->addData( 'page', $page );
		$this->addData( 'limit', $limit );
	}

	static public function logException( $env, $exception ){
		$config		= $env->getConfig()->getAll( 'module.server_system_log.', TRUE );
		if( $config->get( 'file.active' ) && $config->get( 'file.name' ) ){
			$serial		= base64_encode( serialize( (object) array(
				'message'		=> $exception->getMessage(),
				'code'			=> $exception->getCode(),
				'file'			=> $exception->getFile(),
				'line'			=> $exception->getLine(),
				'traceAsString'	=> $exception->getTraceAsString(),
				'traceAsHtml'	=> UI_HTML_Exception_Trace::render( $exception ),
			) ) );
			error_log( time().":".$serial."\n", 3, $config->get( 'file.name' ) );
		}
	}

	static public function mailException( $env, $exception ){
		$config		= $env->getConfig()->getAll( 'module.server_system_log.', TRUE );
		if( $config->get( 'email.active' ) && $config->get( 'email.receivers' ) ){
			$language	= $env->getLanguage()->getLanguage();
			$logic		= new Logic_Mail( $env );
			foreach( explode( ",", $config->get( 'email.receivers' ) ) as $receiver ){
				if( strlen( trim( $receiver ) ) ){
					$user	= (object) array( 'email' => trim( $receiver ) );
					$mail	= new Mail_System_Log_Exception( $env, array( 'exception' => $exception ) );
					try{
						$logic->handleMail( $mail, $user, $language );
//						$mail->sendTo( $user );
						return TRUE;
					}
					catch( Exception $e ){
						$message	= "Sending exception mail failed (".$e->getMessage().").";
						$env->getMessenger()->noteFailure( $message );
						return FALSE;
					}
				}
			}
		}
		return NULL;
	}

	protected function parseLine( $line ){
		list($timestamp, $data)	= explode( ":", $line );
		$data	= base64_decode( $data );
		$object	= unserialize( $data );
		if( !is_object( $object ) )
			throw new InvalidArgumentException( "Line is not containing an exception data object" );
		$object->timestamp	= $timestamp;
		return $object;
	}

	public function remove( $id ){
		$fileName	= $this->moduleConfig->get( 'file.name' );
		if( file_exists( $fileName ) ){
			$content	= trim( FS_File_Reader::load( $fileName ) );
			$lines		= explode( "\n", $content );
			if( count( $lines ) > $id ){
				unset( $lines[$id] );
				$lines	= join( "\n", $lines )."\n";
				FS_File_Writer::save( $fileName, $lines );
			}
		}
		$page	= $this->env->getSession()->get( 'filter_server_system_page' );
		$this->restart( $page ? $page : NULL, TRUE );
	}

	public function view( $id ){
		$fileName	= $this->moduleConfig->get( 'file.name' );
		if( !( $fileName && file_exists( $fileName ) ) ){
			$this->env->getMessenger()->noteError( 'No exception log found.' );
			$this->restart( NULL, TRUE );
		}
		$content	= trim( FS_File_Reader::load( $fileName ) );
		$lines		= explode( "\n", $content );
		if( count( $lines ) <= $id ){
			$this->env->getMessenger()->noteError( 'Invalid exception number.' );
			$this->restart( NULL, TRUE );
		}
		$exception	= $this->parseLine( $lines[$id] );
		$exception->id	= $id;
		$this->addData( 'exception', $exception );
		$this->addData( 'page', $this->env->getSession()->get( 'filter_server_system_page' ) );
	}

	public function logTestException( $message, $code = 0 ){
		$exception	= new Exception( $message, $code );
//		$this->callHook( 'Server:System', 'logException', $this, $exception );
//		self::handleException( $this->env, $exception );
		self::logException( $this->env, $exception );
		$this->restart( NULL, TRUE );
	}
}
?>
