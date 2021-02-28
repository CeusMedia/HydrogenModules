<?php
/**
 *	Data Model of Shipping Prices.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.08.2006
 */
/**
 *	Data Model of Shipping Prices.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.08.2006
 */
final class Model_Shop_Shipping_Price extends CMF_Hydrogen_Model
{
	protected $name		= 'shop_shipping_prices';

	protected $columns	= array(
		'priceId',
		'zoneId',
		'gradeId',
		'price',
	);

	protected $primaryKey	= 'priceId';

	protected $indices		= array(
		'zoneId',
		'gradeId',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
