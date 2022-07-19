<?php
/**
 *	Data model of mail group roles.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data model of mail group roles.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Mail_Group_Role extends Model
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
