<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Shop_Payment_Stripe extends Model
{
	public const STATUS_CREATED		= 0;
	public const STATUS_FAILED		= 1;
	public const STATUS_SUCCEEDED	= 2;

	public const STATUSES			= [
		self::STATUS_CREATED,
		self::STATUS_FAILED,
		self::STATUS_SUCCEEDED,
	];

	protected string $name			= 'shop_payments_stripe';

	protected array $columns		= [
		'paymentId',
		'orderId',
		'userId',
		'payInId',
		'status',
		'object',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'paymentId';

	protected array $indices		= [
		'orderId',
		'userId',
		'payInId',
		'status',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
