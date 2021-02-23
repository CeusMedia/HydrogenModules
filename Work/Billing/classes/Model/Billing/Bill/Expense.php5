<?php
class Model_Billing_Bill_Expense extends CMF_Hydrogen_Model
{
	const STATUS_NEW		= 0;
	const STATUS_BOOKED		= 1;

	const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_BOOKED,
	];

	protected $name		= "billing_bill_expenses";

	protected $columns	= array(
		'billExpenseId',
		'billId',
		'status',
		'amount',
		'title',
	);

	protected $primaryKey	= 'billExpenseId';

	protected $indices		= array(
		'billId',
		'status',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
