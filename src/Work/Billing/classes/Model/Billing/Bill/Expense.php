<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Billing_Bill_Expense extends Model
{
	public const STATUS_NEW			= 0;
	public const STATUS_BOOKED		= 1;

	public const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_BOOKED,
	];

	protected string $name			= "billing_bill_expenses";

	protected array $columns		= [
		'billExpenseId',
		'billId',
		'status',
		'amount',
		'title',
	];

	protected string $primaryKey	= 'billExpenseId';

	protected array $indices		= [
		'billId',
		'status',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
