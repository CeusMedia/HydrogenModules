<?php
class Model_Billing_Corporation_Expense extends CMF_Hydrogen_Model{

	const STATUS_NEW	= 0;
	const STATUS_BOOKED	= 1;

	protected $name		= "billing_corporation_expenses";
	protected $columns	= array(
		'corporationExpenseId',
		'corporationId',
		'expenseId',
		'status',
		'amount',
		'dateBooked',
	);
	protected $primaryKey	= 'corporationExpenseId';
	protected $indices		= array(
		'corporationId',
		'expenseId',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
