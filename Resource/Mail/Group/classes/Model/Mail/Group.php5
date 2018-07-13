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
class Model_Mail_Group extends CMF_Hydrogen_Model {

	const STATUS_ARCHIVED		= -9;
	const STATUS_DEACTIVATED	= -1;
	const STATUS_NEW			= 0;
	const STATUS_EXISTING		= 1;
	const STATUS_ACTIVATED		= 2;
	const STATUS_WORKING		= 3;

	const TYPE_AUTOJOIN			= 0;
	const TYPE_JOIN				= 1;
	const TYPE_REGISTER			= 2;
	const TYPE_INVITE			= 3;

	const VISIBILITY_PUBLIC		= 0;
	const VISIBILITY_INSIDE		= 1;
	const VISIBILITY_MANAGER	= 2;
	const VISIBILITY_HIDDEN		= 3;


	protected $name		= 'mail_groups';
	protected $columns	= array(
		"mailGroupId",
		"mailGroupServerId",
		"defaultRoleId",
		"managerId",
		"type",
		"visibility",
		"status",
		"title",
		"address",
		"password",
		"bounce",
		"subtitle",
		"description",
		"createdAt",
		"modifiedAt",
	);
	protected $primaryKey	= 'mailGroupId';
	protected $indices		= array(
		"mailGroupServerId",
		"defaultRoleId",
		"managerId",
		"type",
		"visibility",
		"status",
		"title",
		"address",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
