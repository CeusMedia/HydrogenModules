<?php
/**
 *	Data model of mail group servers.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\Database\PDO\Table as DatabaseTable;
use CeusMedia\HydrogenFramework\Model\Database\Table as Model;

/**
 *	Data model of mail group servers.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@template-extends	DatabaseTable<Entity_Mail_Group_Server>
 */
class Model_Mail_Group_Server extends Model
{
	protected string $name				= 'mail_group_servers';

	protected array $columns			= [
		'mailGroupServerId',
		'status',
		'imapHost',
		'imapPort',
		'smtpHost',
		'smtpPort',
		'title',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey		= 'mailGroupServerId';

	protected array $indices			= [
		'status',
		'imapHost',
		'imapPort',
		'smtpHost',
		'smtpPort',
		'title',
	];

	protected int $fetchMode			= PDO::FETCH_CLASS;

	protected ?string $fetchEntityClass	= Entity_Mail_Group_Server::class;
}
