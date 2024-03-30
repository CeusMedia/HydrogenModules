<?php

use CeusMedia\HydrogenFramework\Model;

class Model_IP_Lock_Reason extends Model
{
	public const STATUS_DISABLED	= 0;
	public const STATUS_ENABLED		= 1;

	public const STATUSES			= [
		self::STATUS_DISABLED,
		self::STATUS_ENABLED,
	];

	protected string $name			= 'ip_lock_reasons';

	protected array $columns		= [
		'ipLockReasonId',
		'status',
		'duration',
		'title',
		'description',
		'createdAt',
		'appliedAt',
	];

	protected string $primaryKey	= 'ipLockReasonId';

	protected array $indices		= [
		'status',
		'duration',
		'createdAt',
		'appliedAt',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
