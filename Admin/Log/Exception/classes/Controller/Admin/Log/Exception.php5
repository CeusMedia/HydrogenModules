<?php
/**
 *	System Log Controller.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Syslog.php5 3022 2012-06-26 20:08:10Z christian.wuerker $
 */

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment;

/**
 *	System Log Controller.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@extends		CMF_Hydrogen_Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Syslog.php5 3022 2012-06-26 20:08:10Z christian.wuerker $
 */
class Controller_Admin_Log_Exception extends Controller{

	/**	@var		Environment		$env		Environment instance */
	protected $env;
	protected $messenger;
	protected $moduleConfig;

	protected function __onInit(){
		$this->messenger		= $this->env->getMessenger();
		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.admin.', TRUE );
		$this->logic			= $this->env->getLogic()->get( 'logException' );
		$this->model			= new Model_Log_Exception( $this->env );

		$instances	= array( 'this' => (object) array( 'title' => 'Diese Instanz' ) );
		$path		= $this->env->getConfig()->get( 'path.logs' );
		$fileName	= $this->env->getConfig()->get( 'module.server_log_exception.file.name' );

/*
		$instanceKey	= $this->env->getSession()->get( 'filter_admin_log_exception_instance' );
		$instanceKey 	= !in_array( $instanceKey, array( 'this', 'remote' ) ) ? 'this' : $instanceKey;

		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$instances['remote']	= (object) array( 'title' => 'entfernte Instanz' );
			if( $instanceKey === 'remote' ){
				$frontend	= $this->env->getLogic()->get( 'Frontend' );
				$path		= $frontend->getPath( 'logs' );
				$fileName	= $frontend->getModuleConfigValue( 'Server_Log_Exception', 'file.name' );
			}
		}*/

		$this->addData( 'instances', $instances );
//		$this->addData( 'currentInstance', $instanceKey );
		$this->filePath	= $path.$fileName;
	}

	static public function ___onLogException( Environment $env, $context, $module, $data = [] ){
		if( is_object( $data ) && $data instanceof Exception )
			$data	= array( 'exception' => $data );
		if( !isset( $data['exception'] ) )
			throw new InvalidArgumentException( 'Missing exception in given hook call data' );
		$exception	= $data['exception'];
		self::handleException( $env, $exception );
	}

	protected function count(){
		return $this->model->count();
	}

	public function index( $page = 0, $limit = 20 ){
		$count		= $this->logic->importFromLogFile();
		if( $count )
			$this->messenger->noteNotice( 'Imported %d logged exceptions.', $count );

		$page	= preg_match( "/^[0-9]+$/", $page ) ? (int) $page : 0;
		$limit	= preg_match( "/^[0-9]+$/", $limit ) ? (int) $limit : 20;
		$count	= $this->count();
		if( $page > 0 && $page * $limit >= $count )
			$page = floor( $count / $limit );
		$offset	= $page * $limit;
		$this->env->getSession()->set( 'filter_admin_log_exception_page', $page );
		$this->env->getSession()->set( 'filter_admin_log_exception_limit', $limit );
		$limits	= array( $offset, $limit );
		$lines	= $this->model->getAll( array(), array( 'createdAt' => 'DESC' ), $limits );
		$this->addData( 'exceptions', $lines );
		$this->addData( 'total', $count );
		$this->addData( 'page', $page );
		$this->addData( 'limit', $limit );
	}

	public function remove( $id ){
		$this->model->remove( $id );
		$page	= $this->env->getSession()->get( 'filter_admin_log_exception_page' );
		$this->restart( $page ? $page : NULL, TRUE );
	}

	public function view( $id ){
		$exception	= $this->model->get( $id );
		if( !$exception ){
			$this->messenger->noteError( 'Invalid exception number.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'exception', $exception );
		$this->addData( 'page', $this->env->getSession()->get( 'filter_admin_log_exception_page' ) );
	}

	public function setInstance( $instanceKey ){
		$this->env->getSession()->set( 'filter_admin_log_exception_instance', $instanceKey );
		$this->restart( NULL, TRUE );
	}
}
?>
