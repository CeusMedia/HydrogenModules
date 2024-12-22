<?php

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger;

class Controller_Manage_IP_Lock extends Controller
{
	protected Logic_IP_Lock $logic;
	protected Messenger $messenger;
	protected string $filterSessionPrefix	= 'filter_manage_ip_lock_';

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		$request	= $this->env->getRequest();
		if( $request->get( 'ip' ) && $request->get( 'reasonId' ) ){
			$this->logic->lockIp( $request->get( 'ip' ), $request->get( 'reasonId' ) );
			$this->messenger->noteSuccess( 'Lock added.' );
			$this->restart( NULL, TRUE );
		}
		else{
			$this->addData( 'ip', $request->get( 'ip' ) );
			$this->addData( 'reasons', $this->logic->getReasons() );
			$this->addData( 'reasonId', $request->get( 'reasonId' ) );
		}
	}

	/**
	 *	@param		string		$ipLockId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function cancel( string $ipLockId ): void
	{
		if( $this->logic->cancel( $ipLockId ) )
			$this->messenger->noteSuccess( 'IP lock cancelled.' );
		if( ( $from = $this->env->getRequest()->get( 'from' ) ) )
			$this->restart( $from );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		string		$ipLockId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( string $ipLockId ): void
	{
		$lock	= $this->logic->get( $ipLockId, FALSE );
		if( !$lock ){
			$this->messenger->noteError( 'Invalid lock ID.' );
			$this->restart( NULL, TRUE );
		}
		if( $lock->reason->status < Model_IP_Lock_Reason::STATUS_ENABLED )
			$this->messenger->noteNotice( 'This lock is not active since its reason has been disabled.' );
		$this->addData( 'lock', $lock );
	}

	/**
	 *	@param		int		$limit
	 *	@param		int		$page
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function index( int $limit = 15, int $page = 0 ): void
	{
		$session	= $this->env->getSession();
		$conditions	= [
			'status'	=> '!= -1',
		];
		$order		= [];
		if( $session->get( $this->filterSessionPrefix.'ip' ) )
			$conditions['IP']	= $session->get( $this->filterSessionPrefix.'ip' );
		if( strlen( trim( $session->get( $this->filterSessionPrefix.'status' ) ) ) )
			$conditions['status']	= $session->get( $this->filterSessionPrefix.'status' );
		$sort	= 'lockedAt';
		if( $session->get( $this->filterSessionPrefix.'sort' ) )
			$sort	= $session->get( $this->filterSessionPrefix.'sort' );
		$order	= 'DESC';
		if( $session->get( $this->filterSessionPrefix.'order' ) )
			$order	= $session->get( $this->filterSessionPrefix.'order' );
		$orders	= [$sort => $order];
		$limits	= [$page * $limit, $limit];
		$locks	= $this->logic->getAll( $conditions, $orders, $limits );
		$total	= $this->logic->count( $conditions );

		if( $page * $limit > $total )
			$this->restart( NULL, TRUE );

		$this->addData( 'filterStatus', $session->get( $this->filterSessionPrefix.'status' ) );
		$this->addData( 'filterIp', $session->get( $this->filterSessionPrefix.'ip' ) );
		$this->addData( 'filterSort', $session->get( $this->filterSessionPrefix.'sort' ) );
		$this->addData( 'filterOrder', $session->get( $this->filterSessionPrefix.'order' ) );
		$this->addData( 'locks', $locks );
		$this->addData( 'page', $page );
		$this->addData( 'limit', $limit );
		$this->addData( 'total', $total );
		$this->addData( 'count', count( $locks ) );
	}

	/**
	 *	@param		string		$ipLockId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function lock( string $ipLockId ): void
	{
		if( $this->logic->lock( $ipLockId ) )
			$this->messenger->noteSuccess( 'IP locked.' );
		if( ( $from = $this->env->getRequest()->get( 'from' ) ) )
			$this->restart( $from );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		$reset
	 *	@return		void
	 */
	public function order( $reset = NULL ): void
	{
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();

		if( $reset )
			foreach( $session->getAll() as $key => $value )
				if( preg_match( "/^".$this->filterSessionPrefix."/", $key ) )
					$session->remove( $key );
		if( $request->has( 'ip' ) )
			$session->set( $this->filterSessionPrefix.'ip', $request->get( 'ip' ) );
		if( $request->has( 'status' ) )
			$session->set( $this->filterSessionPrefix.'status', $request->get( 'status' ) );
		if( $request->has( 'sort' ) )
			$session->set( $this->filterSessionPrefix.'sort', $request->get( 'sort' ) );
		if( $request->has( 'order' ) )
			$session->set( $this->filterSessionPrefix.'order', $request->get( 'order' ) );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		string		$ipLockId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function unlock( string $ipLockId ): void
	{
		if( $this->logic->unlock( $ipLockId ) )
			$this->messenger->noteSuccess( 'IP unlocked.' );
		if( ( $from = $this->env->getRequest()->get( 'from' ) ) )
			$this->restart( $from );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logic		= Logic_IP_Lock::getInstance( $this->env );
		$this->messenger	= $this->env->getMessenger();
	}
}
