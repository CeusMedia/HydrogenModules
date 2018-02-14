<?php
/**
 *	Data model of mail group actions.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
/**
 *	Data model of mail group actions.
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Mail_Group_Action extends CMF_Hydrogen_Model {

	const STATUS_FAILED				= -1;
	const STATUS_REGISTERED			= 0;
	const STATUS_HANDLED			= 1;

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
?>
