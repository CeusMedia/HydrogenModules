<?php
/**
 *	Data Model of Customers.
 *	@category		Model
 *	@package		Hydrogen.Module.Shop
 *	@author			Christian Würker <Christian.Wuerker@ceus-media.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Customers.
 *	@category		Model
 *	@package		Hydrogen.Module.Shop
 *	@author			Christian Würker <Christian.Wuerker@ceus-media.de>
 */
class Model_Shop_Customer extends Model
{
	protected string $name			= 'shop_customers';

	protected array $columns		= [
		'customerId',
	];

	protected string $primaryKey	= 'customerId';

	protected array $indices		= [];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
