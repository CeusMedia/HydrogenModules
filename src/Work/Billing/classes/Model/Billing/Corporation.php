<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Billing_Corporation extends Model
{
	const STATUS_DISABLED	= -1;
	const STATUS_NEW		= 0;
	const STATUS_ACTIVE		= 1;

	const STATUSES			= [
		self::STATUS_DISABLED,
		self::STATUS_NEW,
		self::STATUS_ACTIVE,
	];

	protected string $name			= "billing_corporations";

	protected array $columns		= [
		'corporationId',
		'status',
		'title',
		'balance',
		'iban',
		'bic',
	];

	protected string $primaryKey	= 'corporationId';

	protected array $indices		= [
		'status',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
