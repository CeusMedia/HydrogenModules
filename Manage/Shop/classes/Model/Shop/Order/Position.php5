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

	protected $name		= 'orderpositions';
	protected $columns	= array(
		"position_id",
		"order_id",
		"article_id",
		"user_id",
		"quantity",
		"status",
		"created",
		"edited",
	);
	protected $primaryKey	= 'position_id';
	protected $indices		= array(
		"order_id",
		"article_id",
		"user_id",
		"status"
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
