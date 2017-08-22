<?php
class Model_Billing_Bill_Expense extends CMF_Hydrogen_Model{
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
