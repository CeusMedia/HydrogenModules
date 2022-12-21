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
	protected string $name		= 'mail_group_servers';

	protected array $columns	= array(
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

	protected string $primaryKey	= 'mailGroupServerId';

	protected array $indices		= array(
		"status",
		"imapHost",
		"imapPort",
		"smtpHost",
		"smtpPort",
		"title",
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
