<?php
class Controller_Work_Billing_Reserve extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->logic	= new Logic_Billing( $this->env );
		$this->request	= $this->env->getRequest();
		$this->session	= $this->env->getSession();
		$this->modelReserve	= new Model_Billing_Reserve( $this->env );
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			$reserveId		= $this->modelReserve->add( $this->request->getAll() );
			$this->restart( 'edit/'.$reserveId, TRUE );
		}
		$this->addData( 'corporations', $this->logic->getCorporations() );
	}

	public function edit( $reserveId ){
		if( $this->request->has( 'save' ) ){
			$this->modelReserve->edit( $reserveId, $this->request->getAll() );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'reserve', $this->modelReserve->get( $reserveId ) );
		$this->addData( 'corporations', $this->logic->getCorporations() );
	}

	public function index(){
		$reserves	= $this->modelReserve->getAll();
		$this->addData( 'reserves', $reserves );

		$corporations	= array();
		foreach( $this->logic->getCorporations() as $corporation )
			$corporations[$corporation->corporationId]	= $corporation;
		$this->addData( 'corporations', $corporations );
	}

	public function remove( $reserveId ){
		$reserve	= $this->modelReserve->get( $reserveId );
		$this->modelReserve->remove( $reserveId );
		$this->restart( NULL, TRUE );
	}
}
?>
