<?php
/**
 *	Data Model of Customers.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Customers.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 */
class Model_Shop_Customer extends Model
{
	protected string $name			= 'shop_customers';

	protected array $columns		= [
		"customerId",
	];

	protected string $primaryKey	= 'customerId';

	protected array $indices		= [];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
