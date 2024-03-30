<?php

use CeusMedia\HydrogenFramework\Model;

class Model_IP_Lock extends Model
{
	public const STATUS_UNLOCKED		= -2;
	public const STATUS_CANCELLED		= -1;
	public const STATUS_REQUEST_LOCK	= 0;
	public const STATUS_LOCKED			= 1;
	public const STATUS_REQUEST_UNLOCK	= 2;

	public const STATUSES				= [
		self::STATUS_UNLOCKED,
		self::STATUS_CANCELLED,
		self::STATUS_REQUEST_LOCK,
		self::STATUS_LOCKED,
		self::STATUS_REQUEST_UNLOCK,
	];

	protected string $name			= 'ip_locks';

	protected array $columns		= [
		'ipLockId',
		'filterId',
		'reasonId',
		'status',
		'IP',
		'uri',
		'views',
		'lockedAt',
		'visitedAt',
		'unlockedAt',
	];

	protected string $primaryKey	= 'ipLockId';

	protected array $indices		= [
		'reasonId',
		'status',
		'IP',
		'lockedAt',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
