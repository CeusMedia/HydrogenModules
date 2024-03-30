<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Billing_Bill extends Model
{
	public const STATUS_NEW			= 0;
	public const STATUS_BOOKED		= 1;

	public const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_BOOKED,
	];

	protected string $name			= "billing_bills";

	protected array $columns		= [
		'billId',
		'status',
		'number',
		'taxRate',
		'amountTaxed',
		'amountNetto',
		'amountAssigned',
		'dateBooked',
		'title',
	];

	protected string $primaryKey	= 'billId';

	protected array $indices		= [
		'status',
		'number',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
