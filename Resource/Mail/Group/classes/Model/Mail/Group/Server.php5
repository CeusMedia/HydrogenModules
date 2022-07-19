<?php
/**
 *	Data model of mail group servers.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data model of mail group servers.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Mail_Group_Server extends Model
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
