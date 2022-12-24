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
	protected string $name			= 'shop_shipping_countries';

	protected array $columns		= [
		'countryId',
		'zoneId',
		'countryCode',
	];

	protected string $primaryKey	= 'countryId';

	protected array $indices		= [
		'zoneId',
		'countryCode',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
