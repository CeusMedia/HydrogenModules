<?php
/**
 *	Data model of mail group actions.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data model of mail group actions.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Mail_Group_Action extends Model
{
	public const STATUS_FAILED		= -1;
	public const STATUS_REGISTERED	= 0;
	public const STATUS_HANDLED		= 1;

	public const STATUSES			= [
		self::STATUS_FAILED,
		self::STATUS_REGISTERED,
		self::STATUS_HANDLED,
	];

	protected string $name			= 'mail_group_actions';

	protected array $columns		= [
		"mailGroupActionId",
		"mailGroupId",
		"mailGroupMemberId",
		"status",
		"uuid",
		"action",
		"message",
		"createdAt",
		"modifiedAt",
	];

	protected string $primaryKey	= 'mailGroupActionId';

	protected array $indices		= [
		"mailGroupId",
		"mailGroupMemberId",
		"status",
		"uuid",
		"action",
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
