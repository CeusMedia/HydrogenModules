<?php
/**
 *	System Log Controller.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 */

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment;

/**
 *	System Log Controller.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 */
class Controller_Admin_Log_Exception extends Controller
{
	/**	@var		Environment		$env		Environment instance */
	protected $env;

	protected $logic;

	protected $messenger;

	protected $model;

	protected $moduleConfig;

	protected $request;

	protected $filterPrefix		= 'filter_admin_log_exception_';

	public static function ___onLogException( Environment $env, $context, $module, $data = [] )
	{
		if( is_object( $data ) && $data instanceof Exception )
			$data	= array( 'exception' => $data );
		if( !isset( $data['exception'] ) )
			throw new InvalidArgumentException( 'Missing exception in given hook call data' );
		$exception	= $data['exception'];
		self::handleException( $env, $exception );
	}

	public function bulk()
	{
		$action	= $this->request->get( 'type' );
		$from	= $this->request->get( 'from' );
		$ids	= array_filter( explode( ',', $this->request->get( 'ids', '' ) ) );

		switch( $action ){
			case 'remove':
				if( count( $ids ) )
					$this->model->removeByIndex( 'exceptionId', $ids );
				break;
			default:
				break;
		}
		$this->restart( $from, !$from );
	}

	public function filter( $reset = NULL )
	{
        if( $reset ){
            foreach( $this->session->getAll( $this->filterPrefix ) as $key => $value )
                $this->session->remove( $this->filterPrefix.$key );
        }
        else{
            $this->session->set( $this->filterPrefix.'message', $this->request->get( 'message' ) );
            $this->session->set( $this->filterPrefix.'type', $this->request->get( 'type' ) );
			$this->session->set( $this->filterPrefix.'dateStart', $this->request->get( 'dateStart' ) );
			$this->session->set( $this->filterPrefix.'dateEnd', $this->request->get( 'dateEnd' ) );
			$this->session->set( $this->filterPrefix.'order', $this->request->get( 'order' ) );
			$this->session->set( $this->filterPrefix.'direction', $this->request->get( 'direction' ) );
		}
		$this->session->remove( $this->filterPrefix.'page' );
		$this->restart( NULL, TRUE );
	}

	public function index( $page = 0, $limit = 0 )
	{
		$count		= $this->logic->importFromLogFile();
		if( $count )
			$this->messenger->noteNotice( 'Imported %d logged exceptions.', $count );

		$limit	= $limit ?: $this->session->get( $this->filterPrefix.'limit', 10 );

		$filterMessage		= $this->session->get( $this->filterPrefix.'message' );
		$filterType			= $this->session->get( $this->filterPrefix.'type' );
		$filterDateStart	= $this->session->get( $this->filterPrefix.'dateStart' );
		$filterDateEnd		= $this->session->get( $this->filterPrefix.'dateEnd' );

		$conditions		= [];
		if( strlen( trim( $filterMessage ) ) )
			$conditions['message']	= '%'.$filterMessage.'%';
		if( strlen( trim( $filterType ) ) )
			$conditions['type']	= $filterType;
		if( $filterDateStart && $filterDateEnd )
			$conditions['createdAt']	= '>< '.strtotime( $filterDateStart ).' & '.( strtotime( $filterDateEnd ) + 24 * 3600 - 1);
		else if( $filterDateStart )
			$conditions['createdAt']	= '>= '.strtotime( $filterDateStart );
		else if( $filterDateEnd )
			$conditions['createdAt']	= '<= '.( strtotime( $filterDateEnd ) + 24 * 36000 - 1);

		if( strlen( trim( $filterType ) ) )
			$conditions['type']	= $filterType;
		if( strlen( trim( $filterType ) ) )
			$conditions['type']	= $filterType;

		$page	= preg_match( "/^[0-9]+$/", $page ) ? (int) $page : 0;
		$limit	= preg_match( "/^[0-9]+$/", $limit ) ? (int) $limit : 20;
		$count	= $this->model->count( $conditions );
		$pages	= ceil( $count / $limit );
		if( $page > 0 && $page + 1 >= $pages )
			$page = $pages - 1;
		$offset	= $page * $limit;
		$this->session->set( $this->filterPrefix.'page', $page );
		$this->session->set( $this->filterPrefix.'limit', $limit );
		$limits	= array( $offset, $limit );
		$lines	= $this->model->getAll( $conditions, ['createdAt' => 'DESC'], $limits );
		$this->addData( 'exceptions', $lines );
		$this->addData( 'total', $count );
		$this->addData( 'page', $page );
		$this->addData( 'limit', $limit );
		$this->addData( 'filterMessage', $filterMessage );
		$this->addData( 'filterType', $filterType );
		$this->addData( 'filterDateStart', $filterDateStart );
		$this->addData( 'filterDateEnd', $filterDateEnd );

		$types	= $this->model->getDistinct( 'type', [], ['type' => 'ASC'] );
		$this->addData( 'exceptionTypes', $types );
	}

	public function remove( $id )
	{
		$this->model->remove( $id );
		$page	= $this->session->get( $this->filterPrefix.'page' );
		$this->restart( $page ? $page : NULL, TRUE );
	}

	public function setInstance( $instanceKey )
	{
		$this->session->set( $this->filterPrefix.'instance', $instanceKey );
		$this->restart( NULL, TRUE );
	}

	public function view( $id )
	{
		$exception	= $this->model->get( $id );
		if( !$exception ){
			$this->messenger->noteError( 'Invalid exception number.' );
			$this->restart( NULL, TRUE );
		}

		$exceptionEnv		= unserialize( $exception->env );
		$exceptionRequest	= unserialize( $exception->request );
		$exceptionSession	= new ADT_List_Dictionary( unserialize( $exception->session ) ?: [] );

		$user	= NULL;
		if( $exceptionSession->get( 'auth_user_id' ) ){
			$model	= new Model_User( $this->env );
			$user	= $model->get( $exceptionSession->get( 'auth_user_id' ) );
		}

		$this->addData( 'exception', $exception );
		$this->addData( 'exceptionEnv', $exceptionEnv );
		$this->addData( 'exceptionRequest', $exceptionRequest );
		$this->addData( 'exceptionSession', $exceptionSession );
		$this->addData( 'user', $user );

		$this->addData( 'page', $this->session->get( $this->filterPrefix.'page' ) );
	}

	protected function __onInit()
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->messenger		= $this->env->getMessenger();
		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.admin.', TRUE );
		$this->logic			= $this->env->getLogic()->get( 'logException' );
		$this->model			= new Model_Log_Exception( $this->env );

		$instances	= array( 'this' => (object) array( 'title' => 'Diese Instanz' ) );
		$path		= $this->env->getConfig()->get( 'path.logs' );
		$fileName	= $this->env->getConfig()->get( 'module.server_log_exception.file.name' );

/*
		$instanceKey	= $this->session->get( $this->filterPrefix.'instance' );
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
}
