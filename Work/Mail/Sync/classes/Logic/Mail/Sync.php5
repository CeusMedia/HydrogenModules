<?php
class Logic_Mail_Sync extends CMF_Hydrogen_Logic{

	protected $modelHost;
	protected $modelSync;

	public function __onInit(){
		$this->modelHost	= new Model_Mail_Host( $this->env );
		$this->modelSync	= new Model_Mail_Sync( $this->env );
	}

	public function addHost( $data ){
		return $this->modelHost->add( $data );
	}

	public function addSync( $data ){
		return $this->modelSync->add( $data );
	}

	public function editHost( $id, $data ){
		return $this->modelHost->edit( $data );
	}

	public function editSync( $id, $data ){
		return $this->modelSync->edit( $data );
	}

	public function getHost( $id ){
		return $this->modelHost->get( $id );
	}

	public function getSync( $id ){
		return $this->modelSync->get( $id );
	}

	public function getHosts( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelHost->getAll( $conditions, $orders, $limits );
	}

	public function getSyncs( $conditions = array(), $orders = array(), $limits = array() ){
		return $this->modelSync->getAll( $conditions, $orders, $limits );
	}
}
