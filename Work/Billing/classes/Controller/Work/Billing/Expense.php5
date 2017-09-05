<?php
class Controller_Work_Billing_Expense extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->logic	= new Logic_Billing( $this->env );
		$this->request	= $this->env->getRequest();
		$this->session	= $this->env->getSession();
		$this->modelExpense		= new Model_Billing_Expense( $this->env );
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			$data	= array(
				'status'		=> $this->request->get( 'status' ),
				'title'			=> $this->request->get( 'title' ),
				'amount'		=> $this->request->get( 'amount' ),
				'frequency'		=> $this->request->get( 'frequency' ),
			);
			if( $this->request->get( 'fromType' ) == 2 )
				$data['fromCorporationId']	= $this->request->get( 'fromCorporationId' );
			else if( $this->request->get( 'fromType' ) == 1 )
				$data['fromPersonId']	= $this->request->get( 'fromPersonId' );
			if( $this->request->get( 'toType' ) == 2 )
				$data['toCorporationId']	= $this->request->get( 'toCorporationId' );
			else if( $this->request->get( 'toType' ) == 1 )
				$data['toPersonId']	= $this->request->get( 'toPersonId' );
			$expenseId		= $this->modelExpense->add( $data );
			$this->restart( 'edit/'.$expenseId, TRUE );
		}
		$this->addData( 'corporations', $this->logic->getCorporations() );
		$this->addData( 'persons', $this->logic->getPersons() );
	}

	public function edit( $expenseId ){
		if( $this->request->has( 'save' ) ){
			$data	= array(
				'title'				=> $this->request->get( 'title' ),
				'amount'			=> $this->request->get( 'amount' ),
				'frequency'			=> $this->request->get( 'frequency' ),
				'status'			=> $this->request->get( 'status' ),
				'fromCorporationId'	=> 0,
				'fromPersonId'		=> 0,
				'toCorporationId'	=> 0,
				'toPersonId'		=> 0,
			);
			if( $this->request->get( 'fromType' ) == 2 )
				$data['fromCorporationId']	= $this->request->get( 'fromCorporationId' );
			else if( $this->request->get( 'fromType' ) == 1 )
				$data['fromPersonId']		= $this->request->get( 'fromPersonId' );
			if( $this->request->get( 'toType' ) == 2 )
				$data['toCorporationId']	= $this->request->get( 'toCorporationId' );
			else if( $this->request->get( 'toType' ) == 1 )
				$data['toPersonId']	= $this->request->get( 'toPersonId' );
			$this->modelExpense->edit( $expenseId, $data );
			$this->restart( NULL, TRUE );
//			$this->restart( './work/billing/expense/edit/'.$expenseId );
		}
		$this->addData( 'expense', $this->logic->getExpense( $expenseId ) );
		$this->addData( 'corporations', $this->logic->getCorporations() );
		$this->addData( 'persons', $this->logic->getPersons() );
	}

	public function index(){
		$expenses	= $this->logic->getExpenses();
		$this->addData( 'expenses', $expenses );

		$corporations	= array();
		foreach( $this->logic->getCorporations() as $corporation )
			$corporations[$corporation->corporationId]	= $corporation;
		$this->addData( 'corporations', $corporations );

		$persons	= array();
		foreach( $this->logic->getPersons() as $person )
			$persons[$person->personId]	= $person;
		$this->addData( 'persons', $persons );
	}

	public function remove( $expenseId ){
		$expense	= $this->modelExpense->get( $expenseId );
		$this->modelExpense->remove( $expenseId );
		$this->restart( NULL, TRUE );
	}
}
?>
