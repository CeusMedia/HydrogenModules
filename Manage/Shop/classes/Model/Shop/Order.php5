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
