<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Shop_Payment_Paypal extends Model
{
	protected string $name		= 'shop_payments_paypal';

	protected array $columns	= [
		'paymentId',
		'orderId',
		'token',
		'payerId',
//		'transactionId',
		'status',
		'amount',
		'email',
		'firstname',
		'lastname',
		'country',
		'street',
		'city',
		'postcode',
		'timestamp',
	];

	protected string $primaryKey	= 'paymentId';

	protected array $indices		= [
		'orderId',
		'token',
		'payerId',
//		'transactionId',
		'status',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
