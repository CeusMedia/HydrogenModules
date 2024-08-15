<?php
/**
 *	System Log Controller.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

/**
 *	System Log Controller.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */
class Controller_Admin_Log_Exception extends Controller
{
	protected Logic_Log_Exception $logic;

	protected MessengerResource $messenger;

	protected Model_Log_Exception $model;

	protected Dictionary $moduleConfig;

	protected Request $request;

	protected Dictionary $session;

	protected string $filterPrefix		= 'filter_admin_log_exception_';

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function bulk(): void
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

	public function filter( $reset = NULL ): void
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

	/**
	 *	@param		int		$page
	 *	@param		int		$limit
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function index( int $page = 0, int $limit = 0 ): void
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

		$page	= preg_match( "/^[0-9]+$/", $page ) ? $page : 0;
		$limit	= preg_match( "/^[0-9]+$/", $limit ) ? (int) $limit : 20;
		$count	= $this->model->count( $conditions );
		$pages	= ceil( $count / $limit );
		if( $page > 0 && $page + 1 >= $pages )
			$page = $pages - 1;
		$offset	= $page * $limit;
		$this->session->set( $this->filterPrefix.'page', $page );
		$this->session->set( $this->filterPrefix.'limit', $limit );
		$limits	= [$offset, $limit];
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

	/**
	 *	@param		int|string		$id
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( int|string $id ): void
	{
		$this->model->remove( $id );
		$page	= $this->session->get( $this->filterPrefix.'page' );
		$this->restart( $page ?: NULL, TRUE );
	}

	public function setInstance( $instanceKey ): void
	{
		$this->session->set( $this->filterPrefix.'instance', $instanceKey );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		int|string		$id
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function view( int|string $id ): void
	{
		/** @var ?object $exception */
		$exception	= $this->model->get( $id );
		if( !$exception ){
			$this->messenger->noteError( 'Invalid exception number.' );
			$this->restart( NULL, TRUE );
		}

		$exceptionEnv		= unserialize( $exception->env );
		$exceptionRequest	= unserialize( $exception->request );
		$exceptionSession	= new Dictionary( unserialize( $exception->session ) ?: [] );

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

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->messenger		= $this->env->getMessenger();
		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.admin.', TRUE );
		$this->logic			= new Logic_Log_Exception( $this->env );
		$this->model			= new Model_Log_Exception( $this->env );

		$instances	= ['this' => (object) ['title' => 'Diese Instanz']];
//		$path		= $this->env->getConfig()->get( 'path.logs' );
//		$fileName	= $this->env->getConfig()->get( 'module.server_log_exception.file.name' );

/*
		$instanceKey	= $this->session->get( $this->filterPrefix.'instance' );
		$instanceKey 	= !in_array( $instanceKey, ['this', 'remote'] ) ? 'this' : $instanceKey;

		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$instances['remote']	= (object) ['title' => 'entfernte Instanz'];
			if( $instanceKey === 'remote' ){
				$frontend	= $this->env->getLogic()->get( 'Frontend' );
				$path		= $frontend->getPath( 'logs' );
				$fileName	= $frontend->getModuleConfigValue( 'Server_Log_Exception', 'file.name' );
			}
		}*/

		$this->addData( 'instances', $instances );
//		$this->addData( 'currentInstance', $instanceKey );
	}
}
