<?php
/**
 *	Data Model of Shipping Zones.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Shipping Zones.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */
final class Model_Shop_Shipping_Zone extends Model
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
