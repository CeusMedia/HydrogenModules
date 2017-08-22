<?php
class Controller_Work_Billing_Person extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->logic	= new Logic_Billing( $this->env );
		$this->request	= $this->env->getRequest();
		$this->session	= $this->env->getSession();
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			$personId		= $this->logic->addPerson(
				Model_Billing_Person::STATUS_NEW,
				$this->request->get( 'firstname' ),
				$this->request->get( 'surname' ),
				$this->request->get( 'balance' )
			);
			$this->restart( 'edit/'.$personId, TRUE );
		}
	}

	public function edit( $personId ){
//		$this->logic->_realizePersonTransactions( $personId );
		$this->addData( 'person', $this->logic->getPerson( $personId ) );
	}

	public function index(){
		$persons	= $this->logic->getPersons();
		foreach( $persons as $person ){
			$person->payouts	= $this->logic->getPersonPayouts( $person->personId );
		}
		$this->addData( 'persons', $persons );
	}

	public function remove( $personId ){

	}
}
?>
