<?php
class Model_Billing_Corporation_Expense extends CMF_Hydrogen_Model{

	const FREQUENCY_YEARLY		= 1;
	const FREQUENCY_QUARTER		= 2;
	const FREQUENCY_MONTHLY		= 3;
	const FREQUENCY_WEEKLY		= 4;
	const FREQUENCY_DAILY		= 5;

	protected $name		= "billing_corporation_expenses";
	protected $columns	= array(
		'corporationExpenseId',
		'expenseId',
		'corporationId',
		'frequency',
		'amount',
		'dateBooked',
	);
	protected $primaryKey	= 'corporationExpenseId';
	protected $indices		= array(
		'corporationId',
		'frequency',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
