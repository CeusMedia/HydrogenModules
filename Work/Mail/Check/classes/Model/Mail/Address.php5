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
class Model_Mail_Address extends CMF_Hydrogen_Model {

	protected $name		= 'mail_addresses';
	protected $columns	= array(
		"mailAddressId",
		"mailGroupId",
		"status",
		"address",
		"data",
		"createdAt",
		"checkedAt",
	);
	protected $primaryKey	= 'mailAddressId';
	protected $indices		= array(
		"mailGroupId",
		"status",
		"address",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
