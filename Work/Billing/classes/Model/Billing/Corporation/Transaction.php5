<?php
class Model_Billing_Corporation_Transaction extends CMF_Hydrogen_Model{

	const STATUS_NEW		= 0;
	const STATUS_BOOKED		= 1;

	protected $name		= "billing_corporation_transactions";
	protected $columns	= array(
		'corporationTransactionId',
		'corporationId',
		'status',
		'relation',
		'amount',
		'dateCreated',
		'dateBooked',
	);
	protected $primaryKey	= 'corporationTransactionId';
	protected $indices		= array(
		'corporationId',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
