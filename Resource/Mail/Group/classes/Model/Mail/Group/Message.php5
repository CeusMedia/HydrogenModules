<?php
/**
 *	Data model of mail group messages.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
/**
 *	Data model of mail group messages.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Mail_Group_Message extends CMF_Hydrogen_Model
{
	const STATUS_REJECTED	= -1;
	const STATUS_NEW		= 0;
	const STATUS_STALLED	= 1;
	const STATUS_FORWARDED	= 2;

	const STATUSES					= [
		self::STATUS_REJECTED,
		self::STATUS_NEW,
		self::STATUS_STALLED,
		self::STATUS_FORWARDED,
	];

	protected $name		= 'mail_group_messages';

	protected $columns	= array(
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
	);

	protected $primaryKey	= 'mailGroupMessageId';

	protected $indices		= array(
		"mailGroupId",
		"mailGroupMemberId",
		"status",
		"parentId",
		"messageId",
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
