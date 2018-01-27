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
class Model_Mail_Group_Role extends CMF_Hydrogen_Model {

	protected $name		= 'mail_group_roles';
	protected $columns	= array(
		"mailGroupRoleId",
		"status",
		"rank",
		"title",
		"read",
		"write",
		"createdAt",
		"modifiedAt",
	);
	protected $primaryKey	= 'mailGroupRoleId';
	protected $indices		= array(
		"status",
		"rank",
		"title",
		"read",
		"write",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
