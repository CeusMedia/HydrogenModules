<?php

use CeusMedia\HydrogenFramework\Model;

class Model_IP_Lock_Reason extends Model
{
	const STATUS_DISABLED	= 0;
	const STATUS_ENABLED	= 1;

	const STATUSES			= [
		self::STATUS_DISABLED,
		self::STATUS_ENABLED,
	];

	protected string $name		= 'ip_lock_reasons';

	protected array $columns	= array(
		'ipLockReasonId',
		'status',
		'duration',
		'title',
		'description',
		'createdAt',
		'appliedAt',
	);

	protected string $primaryKey	= 'ipLockReasonId';

	protected array $indices		= array(
		'status',
		'duration',
		'createdAt',
		'appliedAt',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
