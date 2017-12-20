<?php
/**
 *	Data Model of Order Positions.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.07.2006
 *	@version		3.0
 */
/**
 *	Data Model of Order Positions.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Neon_Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.07.2006
 *	@version		3.0
 */
class Model_Shop_Order_Position extends CMF_Hydrogen_Model {

	const STATUS_NEW		= 0;
	const STATUS_ORDERED	= 1;
	const STATUS_DELIVERED	= 2;

	protected $name		= 'shop_order_positions';
	protected $columns	= array(
		"positionId",
		"orderId",
		"userId",
		"bridgeId",
		"articleId",
		"quantity",
		"currency",
		"price",
		"priceTaxed",
		"status",
		"createdAt",
		"modifiedAt",
	);
	protected $primaryKey	= 'positionId';
	protected $indices		= array(
		"orderId",
		"userId",
		"bridgeId",
		"articleId",
		"currency",
		"status"
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
