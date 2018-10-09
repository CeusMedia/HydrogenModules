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

	protected $pathLogs;
	protected $moduleConfig;

	protected function __onInit(){
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.server_log_exception.', TRUE );
		$this->pathLogs		= $this->env->getConfig()->get( 'path.logs' );

	}

	public function index( $page = 0, $limit = 10 ){
		$page	= preg_match( "/^[0-9]+$/", $page ) ? (int) $page : 0;
		if( $page > 0 && $page * $limit >= $this->count() )
			$page--;
		$limit	= preg_match( "/^[0-9]+$/", $limit ) ? (int) $limit : 10;
		$this->env->getSession()->set( 'filter_server_system_page', $page );
		$this->env->getSession()->set( 'filter_server_system_limit', $limit );
		$lines	= $this->getLinesFromLog( $limit, $page * $limit );
		$this->addData( 'exceptions', $lines );
		$this->addData( 'total', $this->count() );
		$this->addData( 'page', $page );
		$this->addData( 'limit', $limit );
	}

	static public function logException( CMF_Hydrogen_Environment $env, $exception ){
		$env->getCaptain()->callHook( 'Env', 'logException', $env, array( 'exception' => $exception ) );
	}

	public function logTestException( $message, $code = 0 ){
		$exception	= new Exception( $message, $code );
//		$this->callHook( 'Env', 'logException', $this, $exception );
//		self::handleException( $this->env, $exception );
		self::logException( $this->env, $exception );
		$this->restart( NULL, TRUE );
	}

	public function remove( $id ){
		$fileName	= $this->pathLogs.$this->moduleConfig->get( 'file.name' );
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
		$fileName	= $this->pathLogs.$this->moduleConfig->get( 'file.name' );
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

	/*  --  PROTECTED  --  */

	protected function count(){
		$fileName	= $this->pathLogs.$this->moduleConfig->get( 'file.name' );
		if( !file_exists( $fileName ) )
			return 0;
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
		$fileName	= $this->pathLogs.$this->moduleConfig->get( 'file.name' );
		if( !file_exists( $fileName ) )
			return array();
#			throw new Exception_IO( 'Log not existing', 0, 'Log folder' );
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

	protected function parseLine( $line ){
		list($timestamp, $data)	= explode( ":", $line );
		$data	= base64_decode( $data );
		$object	= unserialize( $data );
		if( !is_object( $object ) )
			throw new InvalidArgumentException( "Line is not containing an exception data object" );
		$object->timestamp	= $timestamp;
		return $object;
	}

}
?>
