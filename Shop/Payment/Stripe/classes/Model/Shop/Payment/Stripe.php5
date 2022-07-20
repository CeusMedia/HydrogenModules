<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Shop_Payment_Stripe extends Model
{
	const STATUS_CREATED	= 0;
	const STATUS_FAILED		= 1;
	const STATUS_SUCCEEDED	= 2;

	const STATUSES			= [
		self::STATUS_CREATED,
		self::STATUS_FAILED,
		self::STATUS_SUCCEEDED,
	];

	protected $name		= 'shop_payments_stripe';

	protected $columns	= array(
		'paymentId',
		'orderId',
		'userId',
		'payInId',
		'status',
		'object',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'paymentId';

	protected $indices		= array(
		'orderId',
		'userId',
		'payInId',
		'status',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
