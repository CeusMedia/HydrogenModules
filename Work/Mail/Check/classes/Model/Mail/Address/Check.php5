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
class Model_Mail_Address_Check extends CMF_Hydrogen_Model {

	protected $name		= 'mail_address_checks';
	protected $columns	= array(
		"mailAddressCheckId",
		"mailAddressId",
		"status",
		"error",
		"code",
		"message",
		"createdAt",
	);
	protected $primaryKey	= 'mailAddressCheckId';
	protected $indices		= array(
		"mailAddressId",
		"status",
		"error",
		"code",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
