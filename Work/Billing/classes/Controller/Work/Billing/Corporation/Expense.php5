<?php
class Controller_Work_Billing_Corporation_Expense extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->filterPrefix		= 'filter_work_billing_corporation_expense_';
		$this->logic			= new Logic_Billing( $this->env );
		$this->modelExpense		= new Model_Billing_Corporation_Expense( $this->env );

		if( !$this->session->has( $this->filterPrefix.'year' ) )
			$this->session->set( $this->filterPrefix.'year', date( 'Y' ) );
		if( !$this->session->has( $this->filterPrefix.'month' ) )
			$this->session->set( $this->filterPrefix.'month', date( 'm' ) );
	}

	public function add( $corporationId ){
		$dateBooked	= date( 'Y-m-d' );
		if( $this->request->get( 'dateBooked' ) )
			$dateBooked	= $this->request->get( 'dateBooked' );
		$this->modelExpense->add( array(
			'corporationId'		=> $corporationId,
			'status'		=> Model_Billing_Corporation_Expense::STATUS_NEW,
			'amount'		=> $this->request->get( 'amount' ),
			'title'			=> $this->request->get( 'title' ),
			'dateBooked'	=> $dateBooked,
		) );
		$this->logic->_bookCorporationExpenses( $corporationId );
		$this->restart( $corporationId, TRUE );
	}

	public function filter( $corporationId, $reset = FALSE ){
		if( $reset ){
			$this->session->remove( $this->filterPrefix.'year' );
			$this->session->remove( $this->filterPrefix.'month' );
		}
		else{
			$this->session->set( $this->filterPrefix.'year', $this->request->get( 'year' ) );
			$this->session->set( $this->filterPrefix.'month', $this->request->get( 'month' ) );
		}
		$this->restart( $corporationId, TRUE );
	}

	public function index( $corporationId ){

		$filterYear		= $this->session->get( $this->filterPrefix.'year' );
		$filterMonth	= $this->session->get( $this->filterPrefix.'month' );

		$conditions	= array();
		if( $filterYear || $filterMonth ){
			if( $filterYear && $filterMonth )
				$conditions['dateBooked']	= $filterYear.'-'.$filterMonth.'-%';
			else if( $filterYear )
				$conditions['dateBooked']	= $filterYear.'-%';
			else if( $filterMonth )
				$conditions['dateBooked']	= '%-'.$filterMonth.'-%';
		}
		$expenses	= $this->logic->getCorporationExpenses( $corporationId, $conditions );
		$this->addData( 'corporation', $this->logic->getCorporation( $corporationId ) );
		$this->addData( 'expenses', $expenses );
		$this->addData( 'corporationId', $corporationId );
		$this->addData( 'filterYear', $filterYear );
		$this->addData( 'filterMonth', $filterMonth );
	}
}
