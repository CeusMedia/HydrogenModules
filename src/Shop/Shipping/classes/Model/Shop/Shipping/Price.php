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
	protected string $name			= 'shop_shipping_prices';

	protected array $columns		= [
		'priceId',
		'zoneId',
		'gradeId',
		'price',
	];

	protected string $primaryKey	= 'priceId';

	protected array $indices		= [
		'zoneId',
		'gradeId',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
