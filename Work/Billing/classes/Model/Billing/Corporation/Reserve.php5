<?php
class Model_Billing_Corporation_Reserve extends CMF_Hydrogen_Model{

	const STATUS_NEW	= 0;
	const STATUS_BOOKED	= 1;

	protected $name		= "billing_corporation_reserves";
	protected $columns	= array(
		'corporationReserveId',
		'corporationId',
		'reserveId',
		'billId',
		'personId',
		'status',
		'amount',
		'dateBooked',
	);
	protected $primaryKey	= 'corporationReserveId';
	protected $indices		= array(
		'corporationId',
		'reserveId',
		'billId',
		'personId',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
