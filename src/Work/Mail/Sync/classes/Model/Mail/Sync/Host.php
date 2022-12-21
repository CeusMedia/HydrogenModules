<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Mail_Sync_Host extends Model
{
	protected string $name		= 'mail_sync_hosts';

	protected array $columns	= array(
		'mailSyncHostId',
		'ip',
		'host',
		'port',
		'ssl',
		'auth',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'mailSyncHostId';

	protected array $indices		= array(
		'ip',
		'host',
		'port',
		'ssl',
		'auth',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
