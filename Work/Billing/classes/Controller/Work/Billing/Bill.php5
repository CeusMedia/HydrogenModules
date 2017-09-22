<?php
class Controller_Work_Billing_Bill extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->logic		= new Logic_Billing( $this->env );
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->filterPrefix	= 'filter_work_billing_bill_';
		$this->modelBill	= new Model_Billing_Bill( $this->env );

		if( !$this->session->has( $this->filterPrefix.'year' ) )
			$this->session->set( $this->filterPrefix.'year', date( 'Y' ) );
		if( !$this->session->has( $this->filterPrefix.'month' ) )
			$this->session->set( $this->filterPrefix.'month', date( 'm' ) );
		$this->addData( 'filterSessionPrefix', $this->filterPrefix );
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			$billId		= $this->modelBill->add( array(
				'number'		=> $this->request->get( 'number' ),
				'title'			=> $this->request->get( 'title' ),
				'taxRate'		=> $this->request->get( 'taxRate' ),
				'amountNetto'	=> $this->request->get( 'amountNetto' ),
				'amountTaxed'	=> $this->request->get( 'amountTaxed' ),
				'dateBooked'	=> $this->request->get( 'dateBooked' ),
		 	) );
			$this->restart( './work/billing/bill/breakdown/'.$billId );
		}
	}

	public function edit( $billId ){
		if( $this->request->has( 'save' ) ){
			$this->logic->editBill( $billId, $this->request->getAll() );
			$this->restart( './work/billing/bill/breakdown/'.$billId );
		}
		$bill	= $this->logic->getBill( $billId );
		$billShares	= $this->logic->getBillShares( $billId );
		foreach( $billShares as $billShare ){
			$billShare->person	= $this->logic->getPerson( $billShare->personId );
		}

		$reserves		= $this->logic->getReserves();
		$persons		= $this->logic->getPersons();
		$billReserves	= $this->logic->getBillReserves( $billId );
		$billExpenses	= $this->logic->getBillExpenses( $billId );

		$this->addData( 'bill', $bill );
		$this->addData( 'billShares', $billShares );
		$this->addData( 'reserves', $reserves );
		$this->addData( 'persons', $persons );
		$this->addData( 'billReserves', $billReserves );
		$this->addData( 'billExpenses', $billExpenses );

		$this->addData( 'personTransactions', $this->logic->getBillPersonTransactions( $billId ) );
		$this->addData( 'corporationTransactions', $this->logic->getBillCorporationTransactions( $billId ) );
	}

	public function filter( $reset = NULL ){
		if( $reset ){
			$this->session->remove( $this->filterPrefix.'status' );
			$this->session->remove( $this->filterPrefix.'year' );
			$this->session->remove( $this->filterPrefix.'month' );
			$this->session->remove( $this->filterPrefix.'number' );
			$this->session->remove( $this->filterPrefix.'title' );
			$this->session->remove( $this->filterPrefix.'limit' );
		}
		else{
			$this->session->set( $this->filterPrefix.'status', $this->request->get( 'status' ) );
			$this->session->set( $this->filterPrefix.'year', $this->request->get( 'year' ) );
			$this->session->set( $this->filterPrefix.'month', $this->request->get( 'month' ) );
			$this->session->set( $this->filterPrefix.'number', $this->request->get( 'number' ) );
			$this->session->set( $this->filterPrefix.'title', $this->request->get( 'title' ) );
			$this->session->set( $this->filterPrefix.'limit', $this->request->get( 'limit' ) );
		}
		$this->restart( NULL, TRUE );
	}

	public function index( $page = 0 ){
		$filterStatus	= $this->session->get( $this->filterPrefix.'status' );
		$filterYear	= $this->session->get( $this->filterPrefix.'year' );
		$filterMonth	= $this->session->get( $this->filterPrefix.'month' );
		$filterNumber	= $this->session->get( $this->filterPrefix.'number' );
		$filterTitle	= $this->session->get( $this->filterPrefix.'title' );
		$filterLimit	= $this->session->get( $this->filterPrefix.'limit' );
		if( strlen( $filterStatus ) )
			$conditions['status']	= $filterStatus;

		if( $filterYear || $filterMonth ){
			if( $filterYear && $filterMonth )
				$conditions['dateBooked']	= $filterYear.'-'.$filterMonth.'-%';
			else if( $filterYear )
				$conditions['dateBooked']	= $filterYear.'-%';
			else if( $filterMonth )
				$conditions['dateBooked']	= '%-'.$filterMonth.'-%';
		}
		if( $filterNumber )
			$conditions['number']	= '%'.$filterNumber.'%';
		if( $filterTitle )
			$conditions['title']	= '%'.$filterTitle.'%';
		$bills	= $this->logic->getBills( $conditions, array(), array( $page * 15, 15 ) );
		$total	= $this->logic->countBills( $conditions );

		$this->addData( 'bills', $bills );
		$this->addData( 'filterStatus', $filterStatus );
		$this->addData( 'filterYear', $filterYear );
		$this->addData( 'filterMonth', $filterMonth );
		$this->addData( 'filterNumber', $filterNumber );
		$this->addData( 'filterTitle', $filterTitle );
		$this->addData( 'filterLimit', $filterLimit );
		$this->addData( 'limit', 15 );
		$this->addData( 'page', $page );
		$this->addData( 'pages', ceil( $total / 15 ) );
	}
}
