<?php
class Controller_Manage_IP_Lock extends CMF_Hydrogen_Controller{

	protected $logic;
	protected $messenger;

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

	public function lock( $ipLockId ){
		if( $this->logic->lock( $ipLockId ) )
			$this->messenger->noteSuccess( 'IP locked.' );
		$this->restart( NULL, TRUE );
	}

	public function index(){
		$conditions	= array(
			'status'	=> '!=-1',
		);
		$this->addData( 'locks', $this->logic->getAll( $conditions ) );
	}

	public function remove( $ipLockId ){
		if( $this->logic->remove( $ipLockId ) )
			$this->messenger->noteSuccess( 'IP lock cancelled.' );
		$this->restart( NULL, TRUE );
	}

	public function unlock( $ipLockId ){
		if( $this->logic->unlock( $ipLockId ) )
			$this->messenger->noteSuccess( 'IP unlocked.' );
		$this->restart( NULL, TRUE );
	}
}
