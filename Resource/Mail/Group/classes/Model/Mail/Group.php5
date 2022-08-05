<?php
/**
 *	Data model of mail groups.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data model of mail groups.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Mail_Group extends Model
{
	const STATUS_ARCHIVED		= -9;
	const STATUS_DEACTIVATED	= -1;
	const STATUS_NEW			= 0;
	const STATUS_EXISTING		= 1;
	const STATUS_ACTIVATED		= 2;
	const STATUS_WORKING		= 3;

	const STATUSES				= [
		self::STATUS_ARCHIVED,
		self::STATUS_DEACTIVATED,
		self::STATUS_NEW,
		self::STATUS_EXISTING,
		self::STATUS_ACTIVATED,
		self::STATUS_WORKING,
	];

	const TYPE_AUTOJOIN			= 0;
	const TYPE_JOIN				= 1;
	const TYPE_REGISTER			= 2;
	const TYPE_INVITE			= 3;

	const TYPES					= [
		self::TYPE_AUTOJOIN,
		self::TYPE_JOIN,
		self::TYPE_REGISTER,
		self::TYPE_INVITE,
	];

	const VISIBILITY_PUBLIC		= 0;
	const VISIBILITY_INSIDE		= 1;
	const VISIBILITY_MANAGER	= 2;
	const VISIBILITY_HIDDEN		= 3;

	const VISIBILITES			= [
		self::VISIBILITY_PUBLIC,
		self::VISIBILITY_INSIDE,
		self::VISIBILITY_MANAGER,
		self::VISIBILITY_HIDDEN,
	];

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
