<?php
class Model_Billing_Expense extends CMF_Hydrogen_Model{

	const FREQUENCY_YEARLY		= 1;
	const FREQUENCY_QUARTER		= 2;
	const FREQUENCY_MONTHLY		= 3;
	const FREQUENCY_WEEKLY		= 4;
	const FREQUENCY_DAILY		= 5;

	protected $name		= "billing_expenses";
	protected $columns	= array(
		'expenseId',
		'corporationId',
		'personId',
		'frequency',
		'dayOfMonth',
		'amount',
		'title',
	);
	protected $primaryKey	= 'expenseId';
	protected $indices		= array(
		'corporationId',
		'personId',
		'frequency',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
