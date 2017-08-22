<?php
class Controller_Work_Billing_Person_Unbooked extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->logic			= new Logic_Billing( $this->env );
	}

	public function index( $personId ){
		$unpayedBillShares	= $this->logic->getPersonBillShares( $personId, array(
			'status' => Model_Billing_Bill_Share::STATUS_NEW
		) );
		foreach( $unpayedBillShares as $unpayedBillShare )
			$unpayedBillShare->bill	= $this->logic->getBill( $unpayedBillShare->billId );
		$this->addData( 'unpayedBillShares', $unpayedBillShares );
		$this->addData( 'person', $this->logic->getPerson( $personId ) );
	}
}
