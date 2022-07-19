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
	protected $name		= 'shop_customers';

	protected $columns	= array(
		"customerId",
	);

	protected $primaryKey	= 'customerId';

	protected $indices		= [];

	protected $fetchMode	= PDO::FETCH_OBJ;
}
