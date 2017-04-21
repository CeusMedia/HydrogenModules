<?php
/**
 *	Data Model of Customers.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
/**
 *	Data Model of Customers.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
class Model_Mail extends CMF_Hydrogen_Model {

	const STATUS_ABORTED	= -3;
	const STATUS_FAILED		= -2;
	const STATUS_RETRY		= -1;
	const STATUS_NEW		= 0;
	const STATUS_SENDING	= 1;
	const STATUS_SENT		= 2;
	const STATUS_RECEIVED	= 3;
	const STATUS_OPENED		= 4;
	const STATUS_REPLIED	= 5;
	const STATUS_ARCHIVED	= 6;

	protected $name		= 'mails';
	protected $columns	= array(
		"mailId",
		"senderId",
		"receiverId",
		"status",
		"attempts",
		"language",
		"receiverAddress",
		"receiverName",
		"senderAddress",
		"subject",
		"object",
		"enqueuedAt",
		"attemptedAt",
		"sentAt",
	);
	protected $primaryKey	= 'mailId';
	protected $indices		= array(
		"senderId",
		"receiverId",
		"status",
		"attempts",
		"language",
		"receiverAddress",
		"receiverName",
		"senderAddress",
		"subject",
		"enqueuedAt",
		"attemptedAt",
		"sentAt",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
