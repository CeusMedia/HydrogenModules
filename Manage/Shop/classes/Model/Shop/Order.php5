<?php
/**
 *	Data Model of Orders.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.07.2006
 *	@version		3.0
 */
/**
 *	Data Model of Orders.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Neon_Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.07.2006
 *	@version		3.0
 */
class Model_Shop_Order extends CMF_Hydrogen_Model {

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
		"status",
		"channel",
		"options",
		"price",
		"priceTaxed",
		"createdAt",
		"editedAt",
	);
	protected $primaryKey	= 'orderId';
	protected $indices		= array(
		"customerId",
		"sessionId",
		"channel",
		"status"
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
