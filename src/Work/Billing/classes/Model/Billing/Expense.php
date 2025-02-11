<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Billing_Expense extends Model
{
	public const FREQUENCY_YEARLY	= 1;
	public const FREQUENCY_QUARTER	= 2;
	public const FREQUENCY_MONTHLY	= 3;
	public const FREQUENCY_WEEKLY	= 4;
	public const FREQUENCY_DAILY	= 5;

	public const FREQUENCIES		= [
		self::FREQUENCY_YEARLY,
		self::FREQUENCY_QUARTER,
		self::FREQUENCY_MONTHLY,
		self::FREQUENCY_WEEKLY,
		self::FREQUENCY_DAILY,
	];

	public const STATUS_DISABLED	= 0;
	public const STATUS_ACTIVE		= 1;

	public const STATUSES			= [
		self::STATUS_DISABLED,
		self::STATUS_ACTIVE,
	];

	protected string $name			= "billing_expenses";

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'expenseId';

	protected array $indices		= [
		'fromCorporationId',
		'fromPersonId',
		'toCorporationId',
		'toPersonId',
		'status',
		'frequency',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
