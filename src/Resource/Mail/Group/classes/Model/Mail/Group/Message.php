<?php
/**
 *	Data model of mail group messages.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data model of mail group messages.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Mail_Group_Message extends Model
{
	public const STATUS_REJECTED	= -1;
	public const STATUS_NEW			= 0;
	public const STATUS_STALLED		= 1;
	public const STATUS_FORWARDED	= 2;

	public const STATUSES			= [
		self::STATUS_REJECTED,
		self::STATUS_NEW,
		self::STATUS_STALLED,
		self::STATUS_FORWARDED,
	];

	protected string $name			= 'mail_group_messages';

	protected array $columns		= [
		"mailGroupMessageId",
		"mailGroupId",
		"mailGroupMemberId",
		"status",
		"parentId",
		"messageId",
		"raw",
		"object",
		"createdAt",
		"modifiedAt",
	];

	protected string $primaryKey	= 'mailGroupMessageId';

	protected array $indices		= [
		"mailGroupId",
		"mailGroupMemberId",
		"status",
		"parentId",
		"messageId",
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
