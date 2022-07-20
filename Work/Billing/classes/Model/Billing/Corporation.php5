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

	protected $name		= "billing_corporations";

	protected $columns	= array(
		'corporationId',
		'status',
		'title',
		'balance',
		'iban',
		'bic',
	);

	protected $primaryKey	= 'corporationId';

	protected $indices		= array(
		'status',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
