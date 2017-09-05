<?php
class Model_Billing_Transaction extends CMF_Hydrogen_Model{

	const STATUS_NEW		= 0;
	const STATUS_BOOKED		= 1;

	const TYPE_NONE			= 0;
	const TYPE_CORPORATION	= 1;
	const TYPE_PERSON		= 2;
	const TYPE_BILL			= 3;
	const TYPE_RESERVE		= 4;
	const TYPE_EXPENSE		= 5;
	const TYPE_PAYIN		= 6;
	const TYPE_PAYOUT		= 7;

	protected $name		= "billing_transactions";
	protected $columns	= array(
		'transactionId',
		'fromType',
		'fromId',
		'toType',
		'toId',
		'status',
		'relation',
		'amount',
		'title',
		'dateBooked',
	);
	protected $primaryKey	= 'transactionId';
	protected $indices		= array(
		'fromType',
		'fromId',
		'toType',
		'toId',
		'status',
		'relation',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
