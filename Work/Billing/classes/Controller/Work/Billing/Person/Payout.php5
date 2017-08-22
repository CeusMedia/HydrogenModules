<?php
class Controller_Work_Billing_Person_Payout extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->filterPrefix		= 'filter_work_billing_person_payout_';
		$this->logic			= new Logic_Billing( $this->env );
		$this->modelPayout		= new Model_Billing_Person_Payout( $this->env );

		if( !$this->session->has( $this->filterPrefix.'year' ) )
			$this->session->set( $this->filterPrefix.'year', date( 'Y' ) );
		if( !$this->session->has( $this->filterPrefix.'month' ) )
			$this->session->set( $this->filterPrefix.'month', date( 'm' ) );
	}

	public function add( $personId ){
		$dateBooked	= date( 'Y-m-d' );
		if( $this->request->get( 'dateBooked' ) )
			$dateBooked	= $this->request->get( 'dateBooked' );
		$this->modelPayout->add( array(
			'status'		=> Model_Billing_Person_Payout::STATUS_NEW,
			'personId'		=> $personId,
			'amount'		=> $this->request->get( 'amount' ),
			'title'			=> $this->request->get( 'title' ),
			'dateBooked'	=> $dateBooked,
		) );
		$this->logic->_bookPersonPayouts( $personId );
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

		$conditions	= array();
		if( $filterYear || $filterMonth ){
			if( $filterYear && $filterMonth )
				$conditions['dateBooked']	= $filterYear.'-'.$filterMonth.'-%';
			else if( $filterYear )
				$conditions['dateBooked']	= $filterYear.'-%';
			else if( $filterMonth )
				$conditions['dateBooked']	= '%-'.$filterMonth.'-%';
		}
		$payouts	= $this->logic->getPersonPayouts( $personId, $conditions );
		$this->addData( 'person', $this->logic->getPerson( $personId ) );
		$this->addData( 'payouts', $payouts );
		$this->addData( 'personId', $personId );
		$this->addData( 'filterYear', $filterYear );
		$this->addData( 'filterMonth', $filterMonth );
	}
}
