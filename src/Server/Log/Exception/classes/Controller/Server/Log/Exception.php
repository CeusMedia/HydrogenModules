<?php
/**
 *	Server Log Exception Controller.
 *	@category		CeusMedia.Hydrogen.Module
 *	@package		Server.Log.Exception
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media {@link https://ceusmedia.de/}
 */

use CeusMedia\HydrogenFramework\Controller;

/**
 *	Server Log Exception Controller.
 *	@category		CeusMedia.Hydrogen.Module
 *	@package		Server.Log.Exception
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media {@link https://ceusmedia.de/}
 */
class Controller_Server_Log_Exception extends Controller
{
	protected Model_Log_Exception $model;
	protected Logic_Log_Exception $logic;
	protected string $filterPrefix		= 'filter_server_system_';

	/**
	 *	@param		int		$page
	 *	@param		int		$limit
	 *	@return		void
	 */
	public function index( int $page = 0, int $limit = 20 ): void
	{
		$page	= preg_match( "/^[0-9]+$/", $page ) ? $page : 0;

		$conditions	= [];
		$total		= $this->model->count( $conditions );

		while( $page > 0 && $page * $limit >= $total )
			$page--;
		$limit	= preg_match( "/^[0-9]+$/", $limit ) ? $limit : 10;
		$this->env->getSession()->set( $this->filterPrefix.'page', $page );
		$this->env->getSession()->set( $this->filterPrefix.'limit', $limit );

		$orders		= ['createdAt' => 'DESC', 'exceptionId' => 'DESC'];
		$limits		= [$page * $limit, $limit];
		$exceptions	= $this->model->getAll( $conditions, $orders, $limits );

		$this->addData( 'exceptions', $exceptions );
		$this->addData( 'total', $total );
		$this->addData( 'page', $page );
		$this->addData( 'limit', $limit );
	}

	/**
	 *	@param		string		$message
	 *	@param		int			$code
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function logTestException( string $message, int $code = 0 ): void
	{
		$exception	= new Exception( $message, $code );
//		$payload	= ['exception' => $exception ];
//		$this->callHook( 'Env', 'logException', $this, $payload );
//		self::handleException( $this->env, $exception );
		$this->logic->log( $exception );
		$this->restart( NULL, TRUE );
	}

	/**
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( string $id, $test = FALSE ): void
	{
		if( $test )
			throw new Exception( 'Test' );
		$this->check( $id, FALSE );
		$this->model->remove( $id );
		$page	= $this->env->getSession()->get( $this->filterPrefix.'page' );
		$this->restart( $page, TRUE );
	}

	/**
	 *	@param		string		$id
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function view( string $id ): void
	{
		$exception	= $this->check( $id, FALSE );
		$page		= $this->env->getSession()->get( $this->filterPrefix.'page' );
		$this->addData( 'entity', $exception );
		$this->addData( 'page', $page );
	}

	/*  --  PROTECTED  --  */

	/**
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function __onInit(): void
	{
		$this->model		= new Model_Log_Exception( $this->env );
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logic		= $this->env->getLogic()->get( 'logException' );
		$this->logic->importFromLogFile();
	}

	/**
	 *	@param		string		$id
	 *	@param		bool		$strict
	 *	@return		object|array
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function check( string $id, bool $strict = TRUE ): object|array
	{
		$exception	= $this->logic->check( $id, $strict );
		if( !$exception ){
			$this->env->getMessenger()->noteError( 'Invalid exception ID.' );
			$this->restart( NULL, TRUE );
		}
		return $exception;
	}
}
