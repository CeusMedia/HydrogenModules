<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Billing_Bill_Share extends Model
{
	const STATUS_NEW		= 0;
	const STATUS_BOOKED		= 1;

	const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_BOOKED,
	];

	protected $name		= "billing_bill_shares";

	protected $columns	= array(
		'billShareId',
		'billId',
		'personId',
		'corporationId',
		'status',
		'percent',
		'amount',
	);

	protected $primaryKey	= 'billShareId';

	protected $indices		= array(
		'billId',
		'personId',
		'corporationId',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
