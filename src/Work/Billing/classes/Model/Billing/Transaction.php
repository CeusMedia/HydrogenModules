<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Billing_Transaction extends Model
{
	public const STATUS_NEW			= 0;
	public const STATUS_BOOKED		= 1;

	public const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_BOOKED,
	];

	public const TYPE_NONE			= 0;
	public const TYPE_CORPORATION	= 1;
	public const TYPE_PERSON		= 2;
	public const TYPE_BILL			= 3;
	public const TYPE_RESERVE		= 4;
	public const TYPE_EXPENSE		= 5;
	public const TYPE_PAYIN			= 6;
	public const TYPE_PAYOUT		= 7;

	protected string $name			= "billing_transactions";

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'transactionId';

	protected array $indices		= [
		'fromType',
		'fromId',
		'toType',
		'toId',
		'status',
		'relation',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
