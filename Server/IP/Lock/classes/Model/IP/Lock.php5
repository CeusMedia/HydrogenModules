<?php

use CeusMedia\HydrogenFramework\Model;

class Model_IP_Lock extends Model
{
	const STATUS_UNLOCKED		= -2;
	const STATUS_CANCELLED		= -1;
	const STATUS_REQUEST_LOCK	= 0;
	const STATUS_LOCKED			= 1;
	const STATUS_REQUEST_UNLOCK	= 2;

	const STATUSES			= [
		self::STATUS_UNLOCKED,
		self::STATUS_CANCELLED,
		self::STATUS_REQUEST_LOCK,
		self::STATUS_LOCKED,
		self::STATUS_REQUEST_UNLOCK,
	];

	protected string $name		= 'ip_locks';

	protected array $columns	= array(
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
	);

	protected string $primaryKey	= 'ipLockId';

	protected array $indices		= array(
		'reasonId',
		'status',
		'IP',
		'lockedAt',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
