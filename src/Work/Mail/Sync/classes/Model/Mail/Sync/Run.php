<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Mail_Sync_Run extends Model
{
	public const STATUS_FAIL		= -1;
	public const STATUS_NEW			= 0;
	public const STATUS_SUCCESS		= 1;

	public const STATUSES			= [
		self::STATUS_FAIL,
		self::STATUS_NEW,
		self::STATUS_SUCCESS,
	];

	protected string $name			= 'mail_sync_runs';

	protected array $columns		= [
		'mailSyncRunId',
		'mailSyncId',
		'status',
		'message',
		'statistics',
		'output',
		'createdAt',
		'modifiedAt',
	];
	protected string $primaryKey	= 'mailSyncRunId';

	protected array $indices		= [
		'mailSyncId',
		'status',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
