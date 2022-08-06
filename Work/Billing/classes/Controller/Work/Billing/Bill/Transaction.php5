<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Work_Billing_Bill_Transaction extends Controller
{
	protected $logic;

	public function index( $billId )
	{
		$bill	= $this->logic->getBill( $billId );
		$this->addData( 'bill', $bill );
		$this->addData( 'personTransactions', $this->logic->getBillPersonTransactions( $billId ) );
		$this->addData( 'corporationTransactions', $this->logic->getBillCorporationTransactions( $billId ) );
	}

	protected function __onInit()
	{
		$this->logic		= new Logic_Billing( $this->env );
	}
}
