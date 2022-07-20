<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Billing_Bill extends Model
{
	const STATUS_NEW		= 0;
	const STATUS_BOOKED		= 1;

	const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_BOOKED,
	];

	protected $name		= "billing_bills";

	protected $columns	= array(
		'billId',
		'status',
		'number',
		'taxRate',
		'amountTaxed',
		'amountNetto',
		'amountAssigned',
		'dateBooked',
		'title',
	);

	protected $primaryKey	= 'billId';

	protected $indices		= array(
		'status',
		'number',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
