<?php
class Logic_Mail_Sync extends CMF_Hydrogen_Logic{

	protected $modelHost;
	protected $modelSync;

	public function __onInit(){
		$this->modelSync	= new Model_Mail_Sync( $this->env );
		$this->modelHost	= new Model_Mail_Sync_Host( $this->env );
		$this->modelRun		= new Model_Mail_Sync_Run( $this->env );
	}

	public function addSync( $data ){
		$data['createdAt']	= time();
		$data['modifiedAt']	= time();
		return $this->modelSync->add( $data );
	}

	public function addSyncHost( $data ){
		$data['createdAt']	= time();
		$data['modifiedAt']	= time();
		return $this->modelHost->add( $data );
	}

	public function addSyncRun( $syncId ){
		$data	= array(
			'mailSyncId'	=> $syncId,
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		);
		return $this->modelRun->add( $data );
	}

	public function editSync( $id, $data ){
		$data['modifiedAt']	= time();
		return $this->modelSync->edit( $id, $data );
	}

	public function getSync( $id ){
		return $this->modelSync->get( $id );
	}

	public function editSyncHost( $id, $data ){
		$data['modifiedAt']	= time();
		return $this->modelHost->edit( $id, $data );
	}

	public function editSyncRun( $id, $data ){
		$data['modifiedAt']	= time();
		return $this->modelRun->edit( $id, $data );
	}

	public function getSyncHost( $id ){
		return $this->modelHost->get( $id );
	}

	public function getSyncRun( $id ){
		return $this->modelRun->get( $id );
	}

	public function getSyncs( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelSync->getAll( $conditions, $orders, $limits );
	}

	public function getSyncHosts( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelHost->getAll( $conditions, $orders, $limits );
	}

	public function getSyncRuns( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelRun->getAll( $conditions, $orders, $limits );
	}
}
