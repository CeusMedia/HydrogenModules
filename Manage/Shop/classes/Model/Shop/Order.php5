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

	protected $name		= 'orders';
	protected $columns	= array(
			"order_id",
			"customer_id",
			"session_id",
			"options",
			"channel",
			"status",
			"created",
			"edited",
			);
	protected $primaryKey	= 'order_id';
	protected $indices		= array(
		"customer_id",
		"session_id",
		"channel",
		"status"
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
