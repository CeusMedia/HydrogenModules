<?php
/**
 *	Data Model of Shipping Countries.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.08.2006
 *	@version		3.0
 */
/**
 *	Data Model of Shipping Countries.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.08.2006
 *	@version		3.0
 */
final class Model_Shop_Shipping_Country extends CMF_Hydrogen_Model{

	protected $name		= 'shop_shipping_countries';
	protected $columns	= array(
		"shippingcountryId",
		"shippingzoneId",
		"countryId",
	);
	protected $primaryKey	= 'shippingcountryId';
	protected $indices		= array(
		"shippingzoneId",
		"countryId",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
