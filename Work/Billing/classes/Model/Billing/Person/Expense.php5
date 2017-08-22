<?php
class Model_Billing_Person_Expense extends CMF_Hydrogen_Model{

	const STATUS_NEW	= 0;
	const STATUS_BOOKED	= 1;

	protected $name		= "billing_person_expenses";
	protected $columns	= array(
		'personExpenseId',
		'personId',
		'expenseId',
		'status',
		'amount',
		'title',
		'dateBooked',
	);
	protected $primaryKey	= 'personExpenseId';
	protected $indices		= array(
		'personId',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
