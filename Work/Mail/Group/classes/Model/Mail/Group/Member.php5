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
class Model_Mail_Group_Member extends CMF_Hydrogen_Model {

	const STATUS_DEACTIVATED		= -2;
	const STATUS_UNREGISTERED		= -1;
	const STATUS_REGISTERED			= 0;
	const STATUS_ADDED				= 1;
	const STATUS_ACTIVATED			= 2;

	protected $name		= 'mail_group_members';
	protected $columns	= array(
		"mailGroupMemberId",
		"mailGroupId",
		"roleId",
		"status",
		"address",
		"title",
		"createdAt",
		"modifiedAt",
	);
	protected $primaryKey	= 'mailGroupMemberId';
	protected $indices		= array(
		"mailGroupId",
		"status",
		"address",
		"title",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
