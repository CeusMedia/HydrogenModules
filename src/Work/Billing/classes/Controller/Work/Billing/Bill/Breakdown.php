<?php

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Work_Billing_Bill_Breakdown extends Controller
{
	protected HttpRequest $request;
	protected Logic_Billing $logic;
	protected Model_Billing_Bill $modelBill;

	public function addReserve( string $billId ): void
	{
		$reserveId	= $this->request->get( 'reserveId' );
		$this->logic->addBillReserve( $billId, $reserveId );
		$this->restart( $billId, TRUE );
	}

	public function addShare( string $billId ): void
	{
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

	public function addExpense( string $billId ): void
	{
		$title	= $this->request->get( 'title' );
		$amount	= $this->request->get( 'amount' );
		$status	= $this->request->get( 'status' );
		$this->logic->addBillExpense( $billId, $status, $amount, $title );
		$this->restart( $billId, TRUE );
	}

	public function book( string $billId ): void
	{
		$this->logic->closeBill( $billId );
		$this->restart( './work/billing/bill/transaction/'.$billId );
	}

	public function index( string $billId ): void
	{
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
		$corporations	= $this->logic->getCorporations();
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

	public function removeReserve( string $billReserveId ): void
	{
		$billReserve	= $this->logic->getBillReserve( $billReserveId );
		if( !$billReserve )
			$this->restart( NULL, TRUE );
		$this->logic->removeBillReserve( $billReserveId );
		$this->restart( $billReserve->billId, TRUE );
	}

	public function removeShare( string $billShareId ): void
	{
		$billShare	= $this->logic->getBillShare( $billShareId );
		if( !$billShare )
			$this->restart( NULL, TRUE );
		$this->logic->removeBillShare( $billShareId );
		$this->restart( $billShare->billId, TRUE );
	}

	public function removeExpense( string $billExpenseId ): void
	{
		$billExpense	= $this->logic->getBillExpense( $billExpenseId );
		if( !$billExpense )
			$this->restart( NULL, TRUE );
		$this->logic->removeBillExpense( $billExpenseId );
		$this->restart( $billExpense->billId, TRUE );
	}

	protected function __onInit(): void
	{
		$this->logic		= new Logic_Billing( $this->env );
		$this->request		= $this->env->getRequest();
//		$this->session		= $this->env->getSession();
//		$this->filterPrefix	= 'filter_work_billing_bill_';
		$this->modelBill	= new Model_Billing_Bill( $this->env );
	}
}
