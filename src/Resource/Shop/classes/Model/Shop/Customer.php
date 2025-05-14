<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Data Model of Customers.
 *	@category		Model
 *	@package		Hydrogen.Module.Resource_Shop
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Customers.
 *	@category		Model
 *	@package		Hydrogen.Module.Resource_Shop
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Shop_Customer extends Model
{
	protected string $name			= 'shop_customers';

	protected array $columns		= [
		'customerId',
	];

	protected string $primaryKey	= 'customerId';

	protected array $indices		= [];

	protected int $fetchMode		= PDO::FETCH_CLASS;

	protected ?string $className	= Entity_Shop_Customer::class;
}
