<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Work_Billing_Bill extends Controller
{
	protected Request $request;
	protected Dictionary $session;
	protected Logic_Billing $logic;
	protected Model_Billing_Bill $modelBill;
	protected string $filterPrefix;

	public function add(): void
	{
		if( $this->request->has( 'save' ) ){
			$billId		= $this->modelBill->add( [
				'number'		=> $this->request->get( 'number' ),
				'title'			=> $this->request->get( 'title' ),
				'taxRate'		=> $this->request->get( 'taxRate' ),
				'amountNetto'	=> $this->request->get( 'amountNetto' ),
				'amountTaxed'	=> $this->request->get( 'amountTaxed' ),
				'dateBooked'	=> $this->request->get( 'dateBooked' ),
			] );
			$this->restart( './work/billing/bill/breakdown/'.$billId );
		}
	}

	public function edit( string $billId ): void
	{
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

	public function filter( $reset = NULL ): void
	{
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

	public function index( $page = 0 ): void
	{
		$filterStatus	= $this->session->get( $this->filterPrefix.'status' );
		$filterYear		= $this->session->get( $this->filterPrefix.'year' );
		$filterMonth	= $this->session->get( $this->filterPrefix.'month' );
		$filterNumber	= $this->session->get( $this->filterPrefix.'number' );
		$filterTitle	= $this->session->get( $this->filterPrefix.'title' );
		$filterLimit	= $this->session->get( $this->filterPrefix.'limit' );

		$conditions		= [];
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
		$bills	= $this->logic->getBills( $conditions, [], [$page * 15, 15] );
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

	public function unbook( string $billId ): void
	{
		$bill				= $this->logic->getBill( $billId );
		$modelBillShare		= new Model_Billing_Bill_Share( $this->env );
		$modelBillReserve	= new Model_Billing_Bill_Reserve( $this->env );
		$billShares			= $this->logic->getBillShares( $billId );
		$billReserves		= $this->logic->getBillReserves( $billId );
		foreach( $billReserves as $billReserve ){
			$transactions	= $this->logic->getTransactions( [
				'fromType'	=> Model_Billing_Transaction::TYPE_BILL,
				'fromId'	=> $billId,
				'status'	=> Model_Billing_Transaction::STATUS_BOOKED,
				'relation'	=> '%|billReserve:'.$billReserve->billReserveId.'|%',
			] );
			foreach( $transactions as $transaction )
//				remark( 'Revert share transaction '.$transaction->transactionId );
				$this->logic->revertTransaction( $transaction->transactionId );
//			remark( 'Reserve '.$billReserve->billReserveId.': set status from "booked" to "new"' );
			$modelBillReserve->edit( $billReserve->billReserveId, ['status' => Model_Billing_Bill_Reserve::STATUS_NEW] );
		}
		foreach( $billShares as $billShare ){
			$transactions	= $this->logic->getTransactions( [
				'fromType'	=> Model_Billing_Transaction::TYPE_BILL,
				'fromId'	=> $billId,
				'status'	=> Model_Billing_Transaction::STATUS_BOOKED,
				'relation'	=> '%|billShare:'.$billShare->billShareId.'|%',
			] );
			foreach( $transactions as $transaction )
//				remark( 'Revert share transaction '.$transaction->transactionId );
				$this->logic->revertTransaction( $transaction->transactionId );
//			remark( 'Share '.$billShare->billShareId.': set status from 1 to 0' );
			$modelBillShare->edit( $billShare->billShareId, ['status' => Model_Billing_Bill_Share::STATUS_NEW] );
		}
		$this->modelBill->edit( $billId, ['status' => Model_Billing_Bill::STATUS_NEW] );
//		remark( 'Bill '.$bill->billId.': set status from "booked" to "new"' );
//		die;
		$this->restart( './work/billing/bill/breakdown/'.$billId );
	}

	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->logic		= new Logic_Billing( $this->env );
		$this->modelBill	= new Model_Billing_Bill( $this->env );
		$this->filterPrefix	= 'filter_work_billing_bill_';

		if( !$this->session->has( $this->filterPrefix.'year' ) )
			$this->session->set( $this->filterPrefix.'year', date( 'Y' ) );
		if( !$this->session->has( $this->filterPrefix.'month' ) )
			$this->session->set( $this->filterPrefix.'month', date( 'm' ) );
		$this->addData( 'filterSessionPrefix', $this->filterPrefix );
	}
}
