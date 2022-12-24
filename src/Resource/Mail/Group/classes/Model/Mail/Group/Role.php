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
	protected string $name			= 'mail_group_roles';

	protected array $columns		= [
		"mailGroupRoleId",
		"status",
		"rank",
		"title",
		"read",
		"write",
		"createdAt",
		"modifiedAt",
	];

	protected string $primaryKey	= 'mailGroupRoleId';

	protected array $indices		= [
		"status",
		"rank",
		"title",
		"read",
		"write",
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
