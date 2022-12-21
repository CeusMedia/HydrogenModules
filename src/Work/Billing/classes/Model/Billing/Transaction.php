<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Billing_Transaction extends Model
{
	const STATUS_NEW		= 0;
	const STATUS_BOOKED		= 1;

	const STATUSES				= [
		self::STATUS_NEW,
		self::STATUS_BOOKED,
	];

	const TYPE_NONE			= 0;
	const TYPE_CORPORATION	= 1;
	const TYPE_PERSON		= 2;
	const TYPE_BILL			= 3;
	const TYPE_RESERVE		= 4;
	const TYPE_EXPENSE		= 5;
	const TYPE_PAYIN		= 6;
	const TYPE_PAYOUT		= 7;

	protected string $name		= "billing_transactions";

	protected array $columns	= array(
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

	protected string $primaryKey	= 'transactionId';

	protected array $indices		= array(
		'fromType',
		'fromId',
		'toType',
		'toId',
		'status',
		'relation',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
