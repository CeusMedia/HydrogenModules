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
			$expenseId		= $this->modelExpense->add( array(
				'corporationId'	=> $this->request->get( 'corporationId' ),
				'personId'		=> $this->request->get( 'personId' ),
				'status'		=> $this->request->get( 'status' ),
				'personalize'	=> $this->request->get( 'personalize' ),
				'title'			=> $this->request->get( 'title' ),
				'amount'		=> $this->request->get( 'amount' ),
				'frequency'		=> $this->request->get( 'frequency' ),
				'dayOfMonth'	=> $this->request->get( 'dayOfMonth' ),
			) );
			$this->restart( 'edit/'.$expenseId, TRUE );
		}
		$this->addData( 'corporations', $this->logic->getCorporations() );
		$this->addData( 'persons', $this->logic->getPersons() );
	}

	public function edit( $expenseId ){
		if( $this->request->has( 'save' ) ){
			$this->modelExpense->edit( $expenseId, $this->request->getAll() );
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
