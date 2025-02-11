<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Billing_Bill_Share extends Model
{
	public const STATUS_NEW			= 0;
	public const STATUS_BOOKED		= 1;

	public const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_BOOKED,
	];

	protected string $name			= "billing_bill_shares";

	protected array $columns		= [
		'billShareId',
		'billId',
		'personId',
		'corporationId',
		'status',
		'percent',
		'amount',
	];

	protected string $primaryKey	= 'billShareId';

	protected array $indices		= [
		'billId',
		'personId',
		'corporationId',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
