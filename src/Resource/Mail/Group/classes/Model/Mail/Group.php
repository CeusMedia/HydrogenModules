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
	public const STATUS_ARCHIVED		= -9;
	public const STATUS_DEACTIVATED		= -1;
	public const STATUS_NEW				= 0;
	public const STATUS_EXISTING		= 1;
	public const STATUS_ACTIVATED		= 2;
	public const STATUS_WORKING			= 3;

	public const STATUSES				= [
		self::STATUS_ARCHIVED,
		self::STATUS_DEACTIVATED,
		self::STATUS_NEW,
		self::STATUS_EXISTING,
		self::STATUS_ACTIVATED,
		self::STATUS_WORKING,
	];

	public const TYPE_AUTOJOIN			= 0;
	public const TYPE_JOIN				= 1;
	public const TYPE_REGISTER			= 2;
	public const TYPE_INVITE			= 3;

	public const TYPES					= [
		self::TYPE_AUTOJOIN,
		self::TYPE_JOIN,
		self::TYPE_REGISTER,
		self::TYPE_INVITE,
	];

	public const VISIBILITY_PUBLIC		= 0;
	public const VISIBILITY_INSIDE		= 1;
	public const VISIBILITY_MANAGER		= 2;
	public const VISIBILITY_HIDDEN		= 3;

	public const VISIBILITES			= [
		self::VISIBILITY_PUBLIC,
		self::VISIBILITY_INSIDE,
		self::VISIBILITY_MANAGER,
		self::VISIBILITY_HIDDEN,
	];

	protected string $name			= 'mail_groups';

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'mailGroupId';

	protected array $indices		= [
		"mailGroupServerId",
		"defaultRoleId",
		"managerId",
		"type",
		"visibility",
		"status",
		"title",
		"address",
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
