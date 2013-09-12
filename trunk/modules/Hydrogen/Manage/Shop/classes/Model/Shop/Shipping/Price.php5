<?php
/**
 *	Data Model of Shipping Prices.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.08.2006
 *	@version		3.0
 */
/**
 *	Data Model of Shipping Prices.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.08.2006
 *	@version		3.0
 */
final class Model_Shop_Shipping_Price extends CMF_Hydrogen_Model{

	protected $name		= 'shippingprices';
	protected $columns	= array(
		"shippingprice_id",
		"shippingzone_id",
		"shippinggrade_id",
		"price",
	);
	protected $primaryKey	= 'shippingprice_id';
	protected $indices		= array(
		"shippingzone_id",
		"shippinggrade_id",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
