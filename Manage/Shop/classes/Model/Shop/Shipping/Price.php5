<?php
/**
 *	Data Model of Shipping Prices.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Shipping Prices.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */
final class Model_Shop_Shipping_Price extends Model
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
