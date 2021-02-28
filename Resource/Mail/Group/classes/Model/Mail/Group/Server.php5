<?php
/**
 *	Data model of mail group servers.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
/**
 *	Data model of mail group servers.
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Mail_Group_Server extends CMF_Hydrogen_Model
{
	protected $name		= 'mail_group_servers';

	protected $columns	= array(
		"mailGroupServerId",
		"status",
		"imapHost",
		"imapPort",
		"smtpHost",
		"smtpPort",
		"title",
		"createdAt",
		"modifiedAt",
	);

	protected $primaryKey	= 'mailGroupServerId';

	protected $indices		= array(
		"status",
		"imapHost",
		"imapPort",
		"smtpHost",
		"smtpPort",
		"title",
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
