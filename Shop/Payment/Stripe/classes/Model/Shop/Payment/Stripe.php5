<?php
class Model_Shop_Payment_Stripe extends CMF_Hydrogen_Model{

	const STATUS_CREATED	= 0;
	const STATUS_FAILED		= 1;
	const STATUS_SUCCEEDED	= 2;

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
?>
