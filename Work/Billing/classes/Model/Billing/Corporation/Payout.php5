<?php
class Model_Billing_Corporation_Payout extends CMF_Hydrogen_Model{

	const STATUS_NEW	= 0;
	const STATUS_BOOKED	= 1;

	protected $name		= "billing_corporation_payouts";
	protected $columns	= array(
		'corporationPayoutId',
		'corporationId',
		'status',
		'amount',
		'title',
		'dateBooked',
	);
	protected $primaryKey	= 'corporationPayoutId';
	protected $indices		= array(
		'corporationId',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
