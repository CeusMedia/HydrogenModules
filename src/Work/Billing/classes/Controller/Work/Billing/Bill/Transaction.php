<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Work_Billing_Bill_Transaction extends Controller
{
	protected Logic_Billing $logic;

	public function index( string $billId ): void
	{
		$bill	= $this->logic->getBill( $billId );
		$this->addData( 'bill', $bill );
		$this->addData( 'personTransactions', $this->logic->getBillPersonTransactions( $billId ) );
		$this->addData( 'corporationTransactions', $this->logic->getBillCorporationTransactions( $billId ) );
	}

	protected function __onInit(): void
	{
		$this->logic		= new Logic_Billing( $this->env );
	}
}
