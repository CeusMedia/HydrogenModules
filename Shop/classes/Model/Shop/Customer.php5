<?php
/**
 *	Data Model of Customers.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 */
/**
 *	Data Model of Customers.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 */
class Model_Shop_Customer extends CMF_Hydrogen_Model
{
	protected $name		= 'shop_customers';

	protected $columns	= array(
		"customerId",
	);

	protected $primaryKey	= 'customerId';

	protected $indices		= array();

	protected $fetchMode	= PDO::FETCH_OBJ;
}
