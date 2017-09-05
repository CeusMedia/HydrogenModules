<?php
class Controller_Work_Billing_Person_Reserve extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->filterPrefix		= 'filter_work_billing_person_';
		$this->logic			= new Logic_Billing( $this->env );

		if( !$this->session->has( $this->filterPrefix.'year' ) )
			$this->session->set( $this->filterPrefix.'year', date( 'Y' ) );
		if( !$this->session->has( $this->filterPrefix.'month' ) )
			$this->session->set( $this->filterPrefix.'month', date( 'm' ) );
		$this->addData( 'filterSessionPrefix', $this->filterPrefix );
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
		$conditions		= array(
			'fromType'	=> array(
				Model_Billing_Transaction::TYPE_BILL,
				Model_Billing_Transaction::TYPE_RESERVE,
			),
			'toType'	=> Model_Billing_Transaction::TYPE_PERSON,
			'toId'		=> $personId,
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
		$reserves	= $this->logic->getTransactions( $conditions, $orders, $limits );
		$this->addData( 'reserves', $reserves );
		$this->addData( 'person', $this->logic->getPerson( $personId ) );
		$this->addData( 'personId', $personId );
		$this->addData( 'filterYear', $filterYear );
		$this->addData( 'filterMonth', $filterMonth );
	}
}
