<?php
class Controller_Work_Billing_Person_Expense extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->filterPrefix		= 'filter_work_billing_person_expense_';
		$this->logic			= new Logic_Billing( $this->env );
		$this->modelExpense		= new Model_Billing_Person_Expense( $this->env );

		if( !$this->session->has( $this->filterPrefix.'year' ) )
			$this->session->set( $this->filterPrefix.'year', date( 'Y' ) );
		if( !$this->session->has( $this->filterPrefix.'month' ) )
			$this->session->set( $this->filterPrefix.'month', date( 'm' ) );
		$this->addData( 'filterSessionPrefix', $this->filterPrefix );
	}

	public function add( $personId ){
		$this->logic->addTransaction(
			$this->request->get( 'amount' ),
			Model_Billing_Transaction::TYPE_PERSON,
			$personId,
			Model_Billing_Transaction::TYPE_EXPENSE,
			0,
			NULL,
			$this->request->get( 'title' ),
			$this->request->get( 'dateBooked' )
		);
		$this->restart( $personId, TRUE );
	}

	public function filter( $personId, $reset = FALSE ){
		if( $reset ){
			$this->session->remove( $this->filterPrefix.'year' );
			$this->session->remove( $this->filterPrefix.'month' );
		}
		else{
			$this->session->set( $this->filterPrefix.'year', $this->request->get( 'year' ) );
			$this->session->set( $this->filterPrefix.'month', $this->request->get( 'month' ) );
		}
		$this->restart( $personId, TRUE );
	}

	public function index( $personId ){
		$filterYear		= $this->session->get( $this->filterPrefix.'year' );
		$filterMonth	= $this->session->get( $this->filterPrefix.'month' );
		$conditions	= array(
			'fromType'	=> Model_Billing_Transaction::TYPE_PERSON,
			'fromId'	=> $personId,
			'toType'	=> array(
				Model_Billing_Transaction::TYPE_CORPORATION,
				Model_Billing_Transaction::TYPE_EXPENSE,
			)
		);
		if( $filterYear || $filterMonth ){
			if( $filterYear && $filterMonth )
				$conditions['dateBooked']	= $filterYear.'-'.$filterMonth.'-%';
			else if( $filterYear )
				$conditions['dateBooked']	= $filterYear.'-%';
			else if( $filterMonth )
				$conditions['dateBooked']	= '%-'.$filterMonth.'-%';
		}
		$orders		= array( 'dateBooked' => 'ASC', 'transactionId' => 'ASC' );
		$limits		= array();
		$expenses	= $this->logic->getTransactions( $conditions, $orders, $limits );
		$this->addData( 'person', $this->logic->getPerson( $personId ) );
		$this->addData( 'expenses', $expenses );
		$this->addData( 'personId', $personId );
		$this->addData( 'filterYear', $filterYear );
		$this->addData( 'filterMonth', $filterMonth );
	}
}
