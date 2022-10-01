<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Billing_Bill_Reserve extends Model
{
	const STATUS_NEW		= 0;
	const STATUS_BOOKED		= 1;

	const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_BOOKED,
	];

	protected string $name		= "billing_bill_reserves";

	protected array $columns	= array(
		'billReserveId',
		'billId',
		'reserveId',
		'corporationId',
		'personalize',
		'status',
		'percent',
		'amount',
		'title',
	);

	protected string $primaryKey	= 'billReserveId';

	protected array $indices		= array(
		'billId',
		'reserveId',
		'corporationId',
		'personalize',
		'status',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
