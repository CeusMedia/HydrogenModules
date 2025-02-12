<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Billing_Reserve extends Model
{
	public const STATUS_NEW			= 0;
	public const STATUS_BOOKED		= 1;

	public const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_BOOKED,
	];

	protected string $name			= "billing_reserves";

	protected array $columns		= [
		'reserveId',
		'corporationId',
		'status',
		'personalize',
		'percent',
		'amount',
		'title',
	];

	protected string $primaryKey	= 'reserveId';

	protected array $indices		= [
		'corporationId',
		'status',
		'personalize',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
