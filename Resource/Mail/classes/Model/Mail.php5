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

	protected $name		= 'mails';
	protected $columns	= array(
		"mailId",
		"senderId",
		"receiverId",
		"status",
		"attempts",
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
