<?php
class Model_Billing_Expense extends CMF_Hydrogen_Model
{
	const FREQUENCY_YEARLY		= 1;
	const FREQUENCY_QUARTER		= 2;
	const FREQUENCY_MONTHLY		= 3;
	const FREQUENCY_WEEKLY		= 4;
	const FREQUENCY_DAILY		= 5;

	const FREQUENCIES			= [
		self::FREQUENCY_YEARLY,
		self::FREQUENCY_QUARTER,
		self::FREQUENCY_MONTHLY,
		self::FREQUENCY_WEEKLY,
		self::FREQUENCY_DAILY,
	];

	const STATUS_DISABLED		= 0;
	const STATUS_ACTIVE			= 1;

	const STATUSES				= [
		self::STATUS_DISABLED,
		self::STATUS_ACTIVE,
	];

	protected $name		= "billing_expenses";

	protected $columns	= array(
		'expenseId',
		'fromCorporationId',
		'fromPersonId',
		'toCorporationId',
		'toPersonId',
		'status',
		'frequency',
		'dayOfMonth',
		'amount',
		'title',
	);

	protected $primaryKey	= 'expenseId';

	protected $indices		= array(
		'fromCorporationId',
		'fromPersonId',
		'toCorporationId',
		'toPersonId',
		'status',
		'frequency',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
