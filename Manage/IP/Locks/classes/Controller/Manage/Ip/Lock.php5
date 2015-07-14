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

	public function lock( $ipLockId ){
		if( $this->logic->lock( $ipLockId ) )
			$this->messenger->noteSuccess( 'IP locked.' );
		if( ( $from = $this->env->getRequest()->get( 'from' ) ) )
			$this->restart( $from );
		$this->restart( NULL, TRUE );
	}

	public function index( $limit = 15, $page = 0 ){
		$conditions	= array(
			'status'	=> '!=-1',
		);
		$orders	= array( 'lockedAt' => 'DESC' );
		$limits	= array( $page * $limit, $limit );
		$locks	= $this->logic->getAll( $conditions, $orders, $limits );
		$total	= $this->logic->count( $conditions );
		if( $page * $limit > $total )
			$this->restart( NULL, TRUE );

		$this->addData( 'locks', $locks );
		$this->addData( 'page', $page );
		$this->addData( 'limit', $limit );
		$this->addData( 'total', $total );
		$this->addData( 'count', count( $locks ) );
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
