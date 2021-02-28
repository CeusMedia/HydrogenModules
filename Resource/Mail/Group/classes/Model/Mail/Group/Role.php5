<?php
/**
 *	Data model of mail group roles.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
/**
 *	Data model of mail group roles.
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Mail_Group_Role extends CMF_Hydrogen_Model
{
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
