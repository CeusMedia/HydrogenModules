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
	protected string $name		= 'shop_shipping_zones';

	protected array $columns	= array(
		'zoneId',
		'title',
		'fallback',
	);

	protected string $primaryKey	= 'zoneId';

	protected array $indices		= array(
		'fallback',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
