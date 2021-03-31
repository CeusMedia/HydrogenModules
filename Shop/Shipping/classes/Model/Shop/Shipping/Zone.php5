<?php
/**
 *	Data Model of Shipping Zones.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.07.2006
 */
/**
 *	Data Model of Shipping Zones.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.07.2006
 */
final class Model_Shop_Shipping_Zone extends CMF_Hydrogen_Model
{
	protected $name		= 'shop_shipping_zones';

	protected $columns	= array(
		'zoneId',
		'title',
		'fallback',
	);

	protected $primaryKey	= 'zoneId';

	protected $indices		= array(
		'fallback',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
