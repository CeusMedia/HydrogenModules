<?php
class Controller_Work_Billing_Corporation extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->logic	= new Logic_Billing( $this->env );
		$this->request	= $this->env->getRequest();
		$this->session	= $this->env->getSession();
		$this->modelCorporation	= new Model_Billing_Corporation( $this->env );
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			$corporationId		= $this->modelCorporation->add( array(
				'status'	=> Model_Billing_Corporation::STATUS_NEW,
				'title'		=> $this->request->get( 'title' ),
				'balance'	=> $this->request->get( 'balance' ),
				'iban'		=> $this->request->get( 'iban' ),
				'bic'		=> $this->request->get( 'bic' ),
			) );
			$this->restart( 'edit/'.$corporationId, TRUE );
		}
	}

	public function edit( $corporationId ){
		if( $this->request->has( 'save' ) ){
			$this->modelCorporation->edit( $corporationId, array(
				'title'		=> $this->request->get( 'title' ),
				'iban'		=> $this->request->get( 'iban' ),
				'bic'		=> $this->request->get( 'bic' ),
			) );
			$this->restart( 'edit/'.$corporationId, TRUE );
		}
		$this->addData( 'corporation', $this->modelCorporation->get( $corporationId ) );
	}

	public function index(){
		$corporations	= $this->modelCorporation->getAll();
		$this->addData( 'corporations', $corporations );
	}
}
?>
