<?php
/**
 *	Data Model of Shipping Countries.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.08.2006
 */
/**
 *	Data Model of Shipping Countries.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.08.2006
 */
final class Model_Shop_Shipping_Country extends CMF_Hydrogen_Model
{
	protected $name		= 'shop_shipping_countries';

	protected $columns	= array(
		'countryId',
		'zoneId',
		'countryCode',
	);

	protected $primaryKey	= 'countryId';

	protected $indices		= array(
		'zoneId',
		'countryCode',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
