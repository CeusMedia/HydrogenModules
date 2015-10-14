<?php
class Controller_Manage_IP_Lock extends CMF_Hydrogen_Controller{

	protected $logic;
	protected $messenger;
	protected $filterSessionPrefix	= 'filter_manage_ip_lock_';

	public function __onInit(){
		$this->logic		= Logic_IP_Lock::getInstance( $this->env );
		$this->messenger	= $this->env->getMessenger();
	}

	public function add(){
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

	public function edit( $ipLockId ){
		$lock	= $this->logic->get( $ipLockId );
		if( !$lock ){
			$this->messenger->noteError( 'Invalid lock ID.' );
			$this->restart( NULL, TRUE );
		}
		if( $lock->reason->status < 1 )
			$this->messenger->noteNotice( 'This lock is not active since its reason has been disabled.' );
		$this->addData( 'lock', $lock );
	}

	public function index( $limit = 15, $page = 0 ){
		$session	= $this->env->getSession();
		$conditions	= array(
			'status'	=> '!=-1',
		);
		$order		= array();
		if( $session->get( $this->filterSessionPrefix.'ip' ) )
			$conditions['IPv4']	= $session->get( $this->filterSessionPrefix.'ip' );
		if( strlen( trim( $session->get( $this->filterSessionPrefix.'status' ) ) ) )
			$conditions['status']	= $session->get( $this->filterSessionPrefix.'status' );
		$sort	= 'lockedAt';
		if( $session->get( $this->filterSessionPrefix.'sort' ) )
			$sort	= $session->get( $this->filterSessionPrefix.'sort' );
		$order	= 'DESC';
		if( $session->get( $this->filterSessionPrefix.'order' ) )
			$order	= $session->get( $this->filterSessionPrefix.'order' );
		$orders	= array( $sort => $order );
		$limits	= array( $page * $limit, $limit );
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

	public function lock( $ipLockId ){
		if( $this->logic->lock( $ipLockId ) )
			$this->messenger->noteSuccess( 'IP locked.' );
		if( ( $from = $this->env->getRequest()->get( 'from' ) ) )
			$this->restart( $from );
		$this->restart( NULL, TRUE );
	}

	public function order( $reset = NULL ){
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

	public function remove( $ipLockId ){
		if( $this->logic->remove( $ipLockId ) )
			$this->messenger->noteSuccess( 'IP lock cancelled.' );
		if( ( $from = $this->env->getRequest()->get( 'from' ) ) )
			$this->restart( $from );
		$this->restart( NULL, TRUE );
	}

	public function unlock( $ipLockId ){
		if( $this->logic->unlock( $ipLockId ) )
			$this->messenger->noteSuccess( 'IP unlocked.' );
		if( ( $from = $this->env->getRequest()->get( 'from' ) ) )
			$this->restart( $from );
		$this->restart( NULL, TRUE );
	}
}
