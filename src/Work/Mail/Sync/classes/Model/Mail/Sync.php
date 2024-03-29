<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Mail_Sync extends Model
{
	const STATUS_ERROR			= -1;
	const STATUS_NEW			= 0;
	const STATUS_ACTIVE			= 1;
	const STATUS_SYNCHING		= 2;
	const STATUS_SYNCHED		= 3;
	const STATUS_CLOSED			= 4;

	const STATUSES				= [
		self::STATUS_ERROR,
		self::STATUS_NEW,
		self::STATUS_ACTIVE,
		self::STATUS_SYNCHING,
		self::STATUS_SYNCHED,
		self::STATUS_CLOSED,
	];

	protected string $name			= 'mail_syncs';

	protected array $columns		= [
		'mailSyncId',
		'sourceMailHostId',
		'targetMailHostId',
		'status',
		'resync',
		'sourceUsername',
		'targetUsername',
		'sourcePassword',
		'targetPassword',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'mailSyncId';

	protected array $indices		= [
		'sourceMailHostId',
		'targetMailHostId',
		'sourceUsername',
		'targetUsername',
		'status',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
