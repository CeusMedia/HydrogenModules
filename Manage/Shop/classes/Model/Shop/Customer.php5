<?php
/**
 *	Data Model of Customers.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Customers.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Shop_Customer extends Model
{
	protected $name		= 'shop_customers';

	protected $columns	= array(
		"customerId",
	);

	protected $primaryKey	= 'customerId';

	protected $indices		= [];

	protected $fetchMode	= PDO::FETCH_OBJ;
}
