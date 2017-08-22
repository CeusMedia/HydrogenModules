<?php
class Model_Billing_Bill_Reserve extends CMF_Hydrogen_Model{

	const STATUS_NEW	= 0;
	const STATUS_BOOKED	= 1;

	protected $name		= "billing_bill_reserves";
	protected $columns	= array(
		'billReserveId',
		'billId',
		'reserveId',
		'corporationId',
		'personalize',
		'status',
		'percent',
		'amount',
		'title',
	);
	protected $primaryKey	= 'billReserveId';
	protected $indices		= array(
		'billId',
		'reserveId',
		'corporationId',
		'personalize',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
