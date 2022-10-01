<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Shop_Payment_Paypal extends Model
{
	protected string $name		= 'shop_payments_paypal';

	protected array $columns	= array(
		'paymentId',
		'orderId',
		'token',
		'payerId',
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
	);

	protected string $primaryKey	= 'paymentId';

	protected array $indices		= array(
		'orderId',
		'token',
		'payerId',
		'status',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
