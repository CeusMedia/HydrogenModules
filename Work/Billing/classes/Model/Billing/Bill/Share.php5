<?php
class Model_Billing_Bill_Share extends CMF_Hydrogen_Model{

	const STATUS_NEW	= 0;
	const STATUS_BOOKED	= 1;

	protected $name		= "billing_bill_shares";
	protected $columns	= array(
		'billShareId',
		'billId',
		'personId',
		'personPayoutId',
		'status',
		'percent',
		'amount',
	);
	protected $primaryKey	= 'billShareId';
	protected $indices		= array(
		'billId',
		'personId',
		'personPayoutId',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
