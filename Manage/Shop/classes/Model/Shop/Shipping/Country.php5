<?php
/**
 *	Data Model of Shipping Countries.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Shipping Countries.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */
final class Model_Shop_Shipping_Country extends Model
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
