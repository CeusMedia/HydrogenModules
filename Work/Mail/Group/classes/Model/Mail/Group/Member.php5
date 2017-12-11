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

	protected $name		= 'mail_group_members';
	protected $columns	= array(
		"mailGroupMemberId",
		"mailGroupId",
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
