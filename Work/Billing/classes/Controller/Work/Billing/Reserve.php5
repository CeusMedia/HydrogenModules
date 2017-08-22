<?php
class Controller_Work_Billing_Reserve extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->logic	= new Logic_Billing( $this->env );
		$this->request	= $this->env->getRequest();
		$this->session	= $this->env->getSession();
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			$reserveId		= $this->logic->addReserve(
				$this->request->get( 'title' ),
				$this->request->get( 'percent' ),
				$this->request->get( 'amount' ),
				$this->request->get( 'corporationId' )
			);
			$this->restart( 'edit/'.$reserveId, TRUE );
		}
		$this->addData( 'corporations', $this->logic->getCorporations() );
	}

	public function edit( $reserveId ){
		if( $this->request->has( 'save' ) ){
			$this->logic->editReserve( $reserveId, $this->request->getAll() );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'reserve', $this->logic->getReserve( $reserveId ) );
		$this->addData( 'corporations', $this->logic->getCorporations() );
	}

	public function index(){
		$reserves	= $this->logic->getReserves();
		$this->addData( 'reserves', $reserves );

		$corporations	= array();
		foreach( $this->logic->getCorporations() as $corporation )
			$corporations[$corporation->corporationId]	= $corporation;
		$this->addData( 'corporations', $corporations );
	}

	public function remove( $reserveId ){

	}
}
?>
