<?php
/**
 *	Data model of mail group members.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data model of mail group members.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Mail_Group_Member extends Model
{
	public const STATUS_ARCHIVED		= -9;
	public const STATUS_DEACTIVATED		= -3;
	public const STATUS_REJECTED		= -2;
	public const STATUS_UNREGISTERED	= -1;
	public const STATUS_REGISTERED		= 0;
	public const STATUS_CONFIRMED		= 1;
	public const STATUS_ACTIVATED		= 2;

	public const STATUSES				= [
		self::STATUS_ARCHIVED,
		self::STATUS_DEACTIVATED,
		self::STATUS_REJECTED,
		self::STATUS_UNREGISTERED,
		self::STATUS_REGISTERED,
		self::STATUS_CONFIRMED,
		self::STATUS_ACTIVATED,
	];

	protected string $name			= 'mail_group_members';

	protected array $columns		= [
		"mailGroupMemberId",
		"mailGroupId",
		"roleId",
		"status",
		"address",
		"title",
		"createdAt",
		"modifiedAt",
	];

	protected string $primaryKey	= 'mailGroupMemberId';

	protected array $indices		= [
		"mailGroupId",
		"status",
		"address",
		"title",
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
