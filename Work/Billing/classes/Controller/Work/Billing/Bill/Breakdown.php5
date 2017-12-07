<?php
class Controller_Work_Billing_Bill_Breakdown extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->logic		= new Logic_Billing( $this->env );
		$this->request		= $this->env->getRequest();
//		$this->session		= $this->env->getSession();
//		$this->filterPrefix	= 'filter_work_billing_bill_';
		$this->modelBill	= new Model_Billing_Bill( $this->env );
	}

	public function addReserve( $billId ){
		$reserveId	= $this->request->get( 'reserveId' );
		$this->logic->addBillReserve( $billId, $reserveId );
		$this->restart( $billId, TRUE );
	}

	public function addShare( $billId ){
		$type		= (int) $this->request->get( 'type' );
		$percent	= $this->request->get( 'percent' );
		$amount		= $this->request->get( 'amount' );
		switch( $type ){
			case 0:
				$personId	= $this->request->get( 'personId' );
				$this->logic->addBillPersonShare( $billId, $personId, $amount, $percent );
				break;
			case 1:
				$corporationId	= $this->request->get( 'corporationId' );
				$this->logic->addBillCorporationShare( $billId, $corporationId, $amount, $percent );
				break;
		}
		$this->restart( $billId, TRUE );
	}

	public function addExpense( $billId ){
		$title	= $this->request->get( 'title' );
		$amount	= $this->request->get( 'amount' );
		$status	= $this->request->get( 'status' );
		$this->logic->addBillExpense( $billId, 0, $amount, $title );
		$this->restart( $billId, TRUE );
	}

	public function book( $billId ){
		$this->logic->closeBill( $billId );
		$this->restart( './work/billing/bill/transaction/'.$billId );
	}

	public function index( $billId ){
		$bill	= $this->logic->getBill( $billId );
		$billShares	= $this->logic->getBillShares( $billId );
		foreach( $billShares as $billShare ){
			if( $billShare->personId )
				$billShare->person	= $this->logic->getPerson( $billShare->personId );
			else
				$billShare->corporation	= $this->logic->getCorporation( $billShare->corporationId );
		}

		$reserves		= $this->logic->getReserves();
		$persons		= $this->logic->getPersons();
		$corporations		= $this->logic->getCorporations();
		$billReserves	= $this->logic->getBillReserves( $billId );
		$billExpenses	= $this->logic->getBillExpenses( $billId );

		$this->addData( 'bill', $bill );
		$this->addData( 'billShares', $billShares );
		$this->addData( 'reserves', $reserves );
		$this->addData( 'persons', $persons );
		$this->addData( 'corporations', $corporations );
		$this->addData( 'billReserves', $billReserves );
		$this->addData( 'billExpenses', $billExpenses );

//		$this->addData( 'personTransactions', $this->logic->getBillPersonTransactions( $billId ) );
//		$this->addData( 'corporationTransactions', $this->logic->getBillCorporationTransactions( $billId ) );
	}

	public function removeReserve( $billReserveId ){
		$billReserve	= $this->logic->getBillReserve( $billReserveId );
		if( !$billReserve )
			$this->restart( NULL, TRUE );
		$this->logic->removeBillReserve( $billReserveId );
		$this->restart( $billReserve->billId, TRUE );
	}

	public function removeShare( $billShareId ){
		$billShare	= $this->logic->getBillShare( $billShareId );
		if( !$billShare )
			$this->restart( NULL, TRUE );
		$this->logic->removeBillShare( $billShareId );
		$this->restart( $billShare->billId, TRUE );
	}

	public function removeExpense( $billExpenseId ){
		$billExpense	= $this->logic->getBillExpense( $billExpenseId );
		if( !$billExpense )
			$this->restart( NULL, TRUE );
		$this->logic->removeBillExpense( $billExpenseId );
		$this->restart( $billExpense->billId, TRUE );
	}
}
