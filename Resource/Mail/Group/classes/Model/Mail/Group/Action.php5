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
	const STATUS_FAILED				= -1;
	const STATUS_REGISTERED			= 0;
	const STATUS_HANDLED			= 1;

	const STATUSES					= [
		self::STATUS_FAILED,
		self::STATUS_REGISTERED,
		self::STATUS_HANDLED,
	];

	protected $name		= 'mail_group_actions';

	protected $columns	= array(
		"mailGroupActionId",
		"mailGroupId",
		"mailGroupMemberId",
		"status",
		"uuid",
		"action",
		"message",
		"createdAt",
		"modifiedAt",
	);

	protected $primaryKey	= 'mailGroupActionId';

	protected $indices		= array(
		"mailGroupId",
		"mailGroupMemberId",
		"status",
		"uuid",
		"action",
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
