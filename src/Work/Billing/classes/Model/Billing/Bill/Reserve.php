<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Billing_Bill_Reserve extends Model
{
	public const STATUS_NEW			= 0;
	public const STATUS_BOOKED		= 1;

	public const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_BOOKED,
	];

	protected string $name			= "billing_bill_reserves";

	protected array $columns		= [
		'billReserveId',
		'billId',
		'reserveId',
		'corporationId',
		'personalize',
		'status',
		'percent',
		'amount',
		'title',
	];

	protected string $primaryKey	= 'billReserveId';

	protected array $indices		= [
		'billId',
		'reserveId',
		'corporationId',
		'personalize',
		'status',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
