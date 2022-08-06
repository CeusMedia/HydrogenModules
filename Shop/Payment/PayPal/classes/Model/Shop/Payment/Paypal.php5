<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Shop_Payment_Paypal extends Model
{
	protected $name		= 'shop_payments_paypal';

	protected $columns	= array(
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

	protected $primaryKey	= 'paymentId';

	protected $indices		= array(
		'orderId',
		'token',
		'payerId',
		'status',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
