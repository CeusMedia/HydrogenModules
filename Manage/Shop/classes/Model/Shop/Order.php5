<?php
/**
 *	Data Model of Orders.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Orders.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */
class Model_Shop_Order extends Model
{
	const STATUS_REFUNDED			= -6;
	const STATUS_COMPLAINED			= -5;
	const STATUS_NOT_DELIVERED		= -4;
	const STATUS_NOT_PAYED			= -3;
	const STATUS_REVERSED			= -2;
	const STATUS_CANCELLED			= -1;
	const STATUS_NEW				= 0;
	const STATUS_AUTHENTICATED		= 1;
	const STATUS_ORDERED			= 2;
	const STATUS_PAYED				= 3;
	const STATUS_PARTLY_DELIVERED	= 4;
	const STATUS_DELIVERED			= 5;
	const STATUS_COMPLETED			= 6;

	protected $name		= 'shop_orders';

	protected $columns	= array(
		"orderId",
		"customerId",
		"sessionId",
		"userId",
		"options",
		"paymentMethod",
		"paymentId",
		"status",
		"currency",
		"price",
		"priceTaxed",
		"createdAt",
		"modifiedAt",
	);

	protected $primaryKey	= 'orderId';

	protected $indices		= array(
		"customerId",
		"sessionId",
		"userId",
		"paymentMethod",
		"paymentId",
		"status",
		"currency",
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
