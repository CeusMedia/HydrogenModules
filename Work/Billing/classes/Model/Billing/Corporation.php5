<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Billing_Corporation extends Model
{
	const STATUS_DISABLED	= -1;
	const STATUS_NEW		= 0;

	const STATUSES				= [
		self::STATUS_DISABLED,
		self::STATUS_ACTIVE,
	];

	protected string $name		= "billing_corporations";

	protected array $columns	= array(
		'corporationId',
		'status',
		'title',
		'balance',
		'iban',
		'bic',
	);

	protected string $primaryKey	= 'corporationId';

	protected array $indices		= array(
		'status',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
