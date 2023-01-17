<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Work_Billing_Person_Unbooked extends Controller
{
	protected Logic_Billing $logic;

	public function index( $personId )
	{
		$unpayedBillShares	= $this->logic->getPersonBillShares( $personId, [
			'status' => Model_Billing_Bill_Share::STATUS_NEW
		] );
		foreach( $unpayedBillShares as $unpayedBillShare )
			$unpayedBillShare->bill	= $this->logic->getBill( $unpayedBillShare->billId );
		$this->addData( 'unpayedBillShares', $unpayedBillShares );
		$this->addData( 'person', $this->logic->getPerson( $personId ) );
	}

	protected function __onInit(): void
	{
		$this->logic	= new Logic_Billing( $this->env );
	}
}
