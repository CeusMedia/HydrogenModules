<?php
class Logic_Mail_Sync extends CMF_Hydrogen_Logic{

	protected $modelHost;
	protected $modelSync;

	public function __onInit(){
		$this->modelHost	= new Model_Mail_Host( $this->env );
		$this->modelSync	= new Model_Mail_Sync( $this->env );
		$this->modelSyncRun	= new Model_Mail_Sync_Run( $this->env );
	}

	public function addHost( $data ){
		$data['createdAt']	= time();
		$data['modifiedAt']	= time();
		return $this->modelHost->add( $data );
	}

	public function addSync( $data ){
		$data['createdAt']	= time();
		$data['modifiedAt']	= time();
		return $this->modelSync->add( $data );
	}

	public function addSyncRun( $syncId ){
		$data	= array(
			'mailSyncId'	=> $syncId,
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		);
		return $this->modelSyncRun->add( $data );
	}

	public function editHost( $id, $data ){
		$data['modifiedAt']	= time();
		return $this->modelHost->edit( $id, $data );
	}

	public function editSync( $id, $data ){
		$data['modifiedAt']	= time();
		return $this->modelSync->edit( $id, $data );
	}

	public function editSyncRun( $id, $data ){
		$data['modifiedAt']	= time();
		return $this->modelSyncRun->edit( $id, $data );
	}

	public function getHost( $id ){
		return $this->modelHost->get( $id );
	}

	public function getSync( $id ){
		return $this->modelSync->get( $id );
	}

	public function getSyncRun( $id ){
		return $this->modelSyncRun->get( $id );
	}

	public function getHosts( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelHost->getAll( $conditions, $orders, $limits );
	}

	public function getSyncs( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelSync->getAll( $conditions, $orders, $limits );
	}

	public function getSyncRuns( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelSyncRun->getAll( $conditions, $orders, $limits );
	}
}
