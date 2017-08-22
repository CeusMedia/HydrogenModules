<?php
class Model_Billing_Person_Payout extends CMF_Hydrogen_Model{

	const STATUS_NEW	= 0;
	const STATUS_BOOKED	= 1;

	protected $name		= "billing_person_payouts";
	protected $columns	= array(
		'personPayoutId',
		'personId',
		'status',
		'amount',
		'title',
		'dateBooked',
	);
	protected $primaryKey	= 'personPayoutId';
	protected $indices		= array(
		'personId',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
