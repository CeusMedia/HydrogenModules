<?php
class Controller_Work_Mail_Sync extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->request	= $this->env->getRequest();
		$this->logic	= new Logic_Mail_Sync( $this->env );
	}

	public function add(){
	}

	public function addHost(){
		if( $this->request->has( 'save' ) ){
			$host		= $this->request->get( 'host' );
			$ip			= $this->request->get( 'ip' );
			if( !strlen( trim( $ip ) ) )
				$ip	= gethostbyname( $host );

			$this->logic->addSyncHost( array(
				'host'			=> $host,
				'ip'			=> $ip,
				'port'			=> $this->request->get( 'port' ),
				'ssl'			=> (int) $this->request->get( 'ssl' ),
				'auth'			=> $this->request->get( 'auth' ),
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			) );
			$this->restart( NULL, TRUE  );
		}
		$this->addData( 'hosts', $this->logic->getSyncHosts() );
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
				'createdAt'			=> time(),
				'modifiedAt'		=> time(),
			) );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'hosts', $this->logic->getSyncHosts() );
	}

	public function editSync( $id ){
		if( $this->request->has( 'save' ) ){
			$sourceUsername	= $this->request->get( 'sourceUsername' );
			$sourcePassword	= $this->request->get( 'sourcePassword' );
			$targetUsername	= $this->request->get( 'targetUsername' );
			if( $this->request->get( 'sameUsername' ) )
				$targetUsername	= $sourceUsername;
			$targetPassword	= $this->request->get( 'targetPassword' );
			if( $this->request->get( 'samePassword' ) )
				$targetPassword	= $sourcePassword;

			$this->logic->editSync( $id, array(
				'sourceMailHostId'	=> $this->request->get( 'sourceMailHostId' ),
				'targetMailHostId'	=> $this->request->get( 'targetMailHostId' ),
				'resync'			=> $this->request->get( 'resync' ),
				'sourceUsername'	=> $sourceUsername,
				'sourcePassword'	=> $sourcePassword,
				'targetUsername'	=> $targetUsername,
				'targetPassword'	=> $targetPassword,
				'createdAt'			=> time(),
				'modifiedAt'		=> time(),
			) );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'hosts', $this->logic->getHosts() );
		$this->addData( 'sync', $this->logic->getSync( $id ) );
	}

	public function index(){
		$hosts		= $this->logic->getSyncHosts();
		$syncs		= $this->logic->getSyncs();
		foreach( $syncs as $sync ){
			$sync->runs	= $this->logic->getSyncRuns(
				array( 'mailSyncId' => $sync->mailSyncId ),
				array( 'mailSyncId' => 'DESC' )
			);
			if( $sync->runs )
				$sync->run	= $sync->runs[count( $sync->runs ) - 1];
		}
		$this->addData( 'hosts', $hosts );
		$this->addData( 'syncs', $syncs );
	}

	public function setSyncStatus( $id, $status ){
		$this->logic->editSync( $id, array(
			'status'		=> (int) $status,
			'modifiedAt'	=> time(),
		) );
		$this->restart( NULL, TRUE );
	}
}
