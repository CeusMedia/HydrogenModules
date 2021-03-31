<?php
/**
 *	Data model of mail group members.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
/**
 *	Data model of mail group members.
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Mail_Group_Member extends CMF_Hydrogen_Model
{
	const STATUS_ARCHIVED			= -9;
	const STATUS_DEACTIVATED		= -3;
	const STATUS_REJECTED			= -2;
	const STATUS_UNREGISTERED		= -1;
	const STATUS_REGISTERED			= 0;
	const STATUS_CONFIRMED			= 1;
	const STATUS_ACTIVATED			= 2;

	const STATUSES					= [
		self::STATUS_ARCHIVED,
		self::STATUS_DEACTIVATED,
		self::STATUS_REJECTED,
		self::STATUS_UNREGISTERED,
		self::STATUS_REGISTERED,
		self::STATUS_CONFIRMED,
		self::STATUS_ACTIVATED,
	];

	protected $name		= 'mail_group_members';

	protected $columns	= array(
		"mailGroupMemberId",
		"mailGroupId",
		"roleId",
		"status",
		"address",
		"title",
		"createdAt",
		"modifiedAt",
	);

	protected $primaryKey	= 'mailGroupMemberId';

	protected $indices		= array(
		"mailGroupId",
		"status",
		"address",
		"title",
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
