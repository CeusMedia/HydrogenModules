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
		"firstname",
		"lastname",
		"country",
		"region",
		"city",
		"postcode",
		"address",
		"phone",
		"email",
		"password",
		"longitude",
		"latitude",
		"alternative",
		"billing_institution",
		"billing_firstname",
		"billing_lastname",
		"billing_tnr",
		"billing_country",
		"billing_city",
		"billing_postcode",
		"billing_address",
		"billing_phone",
		"billing_email",
	);

	protected $primaryKey	= 'customerId';

	protected $indices		= array(
		"lastname",
		"country",
		"email",
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
