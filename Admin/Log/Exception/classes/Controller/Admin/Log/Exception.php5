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
class Controller_Admin_Log_Exception extends CMF_Hydrogen_Controller{

	/**	@var		Environment		$env		Environment instance */
	protected $env;
	protected $moduleConfig;

	protected function __onInit(){
		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.admin.', TRUE );

		$instances	= array( 'this' => (object) array( 'title' => 'Diese Instanz' ) );
		$path		= $this->env->getConfig()->get( 'path.logs' );
		$fileName	= $this->env->getConfig()->get( 'module.server_log_exception.file.name' );

		$instanceKey	= $this->env->getSession()->get( 'filter_admin_log_exception_instance' );
		$instanceKey 	= !in_array( $instanceKey, array( 'this', 'remote' ) ) ? 'this' : $instanceKey;

		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$instances['remote']	= (object) array( 'title' => 'entfernte Instanz' );
			if( $instanceKey === 'remote' ){
				$frontend	= $this->env->getLogic()->get( 'Frontend' );
				$path		= $frontend->getPath( 'logs' );
				$fileName	= $frontend->getModuleConfigValue( 'Server_Log_Exception', 'file.name' );
			}
		}

		$this->addData( 'instances', $instances );
		$this->addData( 'currentInstance', $instanceKey );
		$this->filePath	= $path.$fileName;
	}

	static public function ___onLogException( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		if( is_object( $data ) && $data instanceof Exception )
			$data	= array( 'exception' => $data );
		if( !isset( $data['exception'] ) )
			throw new InvalidArgumentException( 'Missing exception in given hook call data' );
		$exception	= $data['exception'];
		self::handleException( $env, $exception );
	}

	protected function count(){
		if( !file_exists( $this->filePath ) )
			return 0;
#			throw new RuntimeException( 'Log not existing' );
		$content	= trim( FS_File_Reader::load( $this->filePath ) );
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
		if( !file_exists( $this->filePath ) )
			return array();
#			throw new RuntimeException( 'Log not existing' );
		$content	= trim( FS_File_Reader::load( $this->filePath ) );
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

	public function index( $page = 0, $limit = 10 ){
		$page	= preg_match( "/^[0-9]+$/", $page ) ? (int) $page : 0;
		if( $page > 0 && $page * $limit >= $this->count() )
			$page--;
		$limit	= preg_match( "/^[0-9]+$/", $limit ) ? (int) $limit : 10;
		$this->env->getSession()->set( 'filter_admin_log_exception_page', $page );
		$this->env->getSession()->set( 'filter_admin_log_exception_limit', $limit );
		$lines	= $this->getLinesFromLog( $limit, $page * $limit );
		$this->addData( 'exceptions', $lines );
		$this->addData( 'total', $this->count() );
		$this->addData( 'page', $page );
		$this->addData( 'limit', $limit );
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
		if( file_exists( $this->filePath ) ){
			$content	= trim( FS_File_Reader::load( $this->filePath ) );
			$lines		= explode( "\n", $content );
			if( count( $lines ) > $id ){
				unset( $lines[$id] );
				$lines	= join( "\n", $lines )."\n";
				FS_File_Writer::save( $this->filePath, $lines );
			}
		}
		$page	= $this->env->getSession()->get( 'filter_admin_log_exception_page' );
		$this->restart( $page ? $page : NULL, TRUE );
	}

	public function view( $id ){
		if( !( $this->filePath && file_exists( $this->filePath ) ) ){
			$this->env->getMessenger()->noteError( 'No exception log found.' );
			$this->restart( NULL, TRUE );
		}
		$content	= trim( FS_File_Reader::load( $this->filePath ) );
		$lines		= explode( "\n", $content );
		if( count( $lines ) <= $id ){
			$this->env->getMessenger()->noteError( 'Invalid exception number.' );
			$this->restart( NULL, TRUE );
		}
		$exception	= $this->parseLine( $lines[$id] );
		$exception->id	= $id;
		$this->addData( 'exception', $exception );
		$this->addData( 'page', $this->env->getSession()->get( 'filter_admin_log_exception_page' ) );
	}

	public function setInstance( $instanceKey ){
		$this->env->getSession()->set( 'filter_admin_log_exception_instance', $instanceKey );
		$this->restart( NULL, TRUE );
	}
}
?>
