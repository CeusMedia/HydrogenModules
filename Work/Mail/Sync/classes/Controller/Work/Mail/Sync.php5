<?php
class Controller_Work_Mail_Sync extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->request	= $this->env->getRequest();
		$this->logic	= new Logic_Mail_Sync( $this->env );
	}

	public function add(){
	}

	public function addBox(){
		if( $this->request->has( 'save' ) ){
			$this->logic->addBox( array(
				'mailHostId'	=> $this->request->get( 'hostId' ),
				'username'		=> $this->request->get( 'username' ),
				'password'		=> $this->request->get( 'password' ),
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			) );
			$this->restart( NULL, TRUE  );
		}
		$this->addData( 'hosts', $this->logic->getHosts() );
	}

	public function addHost(){
		if( $this->request->has( 'save' ) ){
			$this->logic->addHost( array(
				'host'			=> $this->request->get( 'host' ),
				'ip'			=> $this->request->get( 'ip' ),
				'port'			=> $this->request->get( 'port' ),
				'ssl'			=> (int) $this->request->get( 'ssl' ),
				'auth'			=> $this->request->get( 'auth' ),
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			) );
			$this->restart( NULL, TRUE  );
		}
		$this->addData( 'hosts', $this->logic->getHosts() );
	}

	public function addSync(){
		if( $this->request->has( 'save' ) ){
			$sourceUsername	= $this->request->get( 'sourceUsername' );
			$sourcePassword	= $this->request->get( 'sourcePassword' );
			$targetUsername	= $this->request->get( 'targetUsername' );
			if( $this->request->get( 'sameUsername' ) )
				$targetUsername	= $sourceUsername;
			$targetPassword	= $this->request->get( 'targetPassword' );
			if( $this->request->get( 'samePassword' ) )
				$targetPassword	= $sourcePassword;

			$this->logic->addSync( array(
				'sourceMailHostId'	=> $this->request->get( 'sourceMailHostId' ),
				'targetMailHostId'	=> $this->request->get( 'targetMailHostId' ),
				'resync'			=> $this->request->get( 'resync' ),
				'sourceUsername'	=> $sourceUsername,
				'sourcePassword'	=> $sourcePassword,
				'targetUsername'	=> $targetUsername,
				'targetPassword'	=> $targetPassword,
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			) );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'hosts', $this->logic->getHosts() );
	}

	public function index(){
		$this->addData( 'hosts', $this->logic->getHosts() );
		$this->addData( 'boxes', $this->logic->getBoxes() );
		$this->addData( 'syncs', $this->logic->getSyncs() );
	}
}
