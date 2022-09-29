<?php
/**
 *	Server Log Exception Controller.
 *	@category		CeusMedia.Hydrogen.Module
 *	@package		Server.Log.Exception
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2019 Ceus Media {@link https://ceusmedia.de/}
 */

use CeusMedia\HydrogenFramework\Controller;

/**
 *	Server Log Exception Controller.
 *	@category		CeusMedia.Hydrogen.Module
 *	@package		Server.Log.Exception
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2019 Ceus Media {@link https://ceusmedia.de/}
 */
class Controller_Server_Log_Exception extends Controller
{
	protected $model;
	protected $logic;

	public function index( $page = 0, $limit = 20 )
	{
		$page	= preg_match( "/^[0-9]+$/", $page ) ? (int) $page : 0;

		$conditions	= [];
		$total		= $this->model->count( $conditions );

		while( $page > 0 && $page * $limit >= $total )
			$page--;
		$limit	= preg_match( "/^[0-9]+$/", $limit ) ? (int) $limit : 10;
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

	public function logTestException( $message, $code = 0 )
	{
		$exception	= new Exception( $message, $code );
//		$this->callHook( 'Env', 'logException', $this, $exception );
//		self::handleException( $this->env, $exception );
		$this->logic->log( $exception );
		$this->restart( NULL, TRUE );
	}

	public function remove( $id, $test = FALSE )
	{
		if( $test )
			throw new Exception( 'Test' );
		$exception	= $this->check( $id, FALSE );
		$this->model->remove( $id );
		$page	= $this->env->getSession()->get( $this->filterPrefix.'page' );
		$this->restart( $page, TRUE );
	}

	public function view( $id )
	{
		$exception	= $this->check( $id, FALSE );
		$page		= $this->env->getSession()->get( $this->filterPrefix.'page' );
		$this->addData( 'exception', $exception );
		$this->addData( 'page', $page );
	}

	/*  --  PROTECTED  --  */

	protected function __onInit()
	{
		$this->model		= new Model_Log_Exception( $this->env );
		$this->logic		= $this->env->getLogic()->get( 'logException');
		$this->logic->importFromLogFile();
		$this->filterPrefix	= 'filter_server_system_';
	}

	protected function check( $id, $strict = TRUE )
	{
		$exception	= $this->logic->check( $id, FALSE );
		if( !$exception ){
			$this->env->getMessenger()->noteError( 'Invalid exception ID.' );
			$this->restart( NULL, TRUE );
		}
		return $exception;
	}
}
