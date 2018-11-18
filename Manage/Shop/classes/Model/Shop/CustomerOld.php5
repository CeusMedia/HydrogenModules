<?php
/**
 *	Data Model of Customers.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
/**
 *	Data Model of Customers.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
class Model_Shop_Customer extends CMF_Hydrogen_Model {

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
?>
