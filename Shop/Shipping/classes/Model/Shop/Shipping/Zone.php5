<?php
/**
 *	Data Model of Shipping Zones.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.07.2006
 *	@version		3.0
 */
/**
 *	Data Model of Shipping Zones.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.07.2006
 *	@version		3.0
 */
final class Model_Shop_Shipping_Zone extends CMF_Hydrogen_Model{

	protected $name		= 'shop_shipping_zones';
	protected $columns	= array(
		"shippingzoneId",
		"title",
	);
	protected $primaryKey	= 'shippingzoneId';
	protected $indices		= array(
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>