<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Billing_Person extends Model
{
	const STATUS_DISABLED	= -1;
	const STATUS_NEW		= 0;
	const STATUS_ACTIVE		= 1;

	const STATUSES			= [
		self::STATUS_DISABLED,
		self::STATUS_NEW,
		self::STATUS_ACTIVE,
	];

	protected string $name			= "billing_persons";

	protected array $columns		= [
		'personId',
		'status',
		'email',
		'firstname',
		'surname',
		'balance',
	];

	protected string $primaryKey	= 'personId';

	protected array $indices		= [
		'status',
		'email',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
