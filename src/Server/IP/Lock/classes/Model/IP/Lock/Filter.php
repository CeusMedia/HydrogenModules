<?php
use CeusMedia\HydrogenFramework\Model;

class Model_IP_Lock_Filter extends Model
{
	public const STATUS_DISABLED_BY_REASON	= -10;
	public const STATUS_DISABLED			= 0;
	public const STATUS_ENABLED				= 1;

	public const STATUSES					= [
		self::STATUS_DISABLED,
		self::STATUS_ENABLED,
	];

	public const LOCK_STATUS_REQUEST		= 0;
	public const LOCK_STATUS_IMMEDIATE		= 1;

	public const LOCK_STATUSES				= [
		self::LOCK_STATUS_REQUEST,
		self::LOCK_STATUS_IMMEDIATE,
	];

	protected string $name			= 'ip_lock_filters';

	protected array $columns		= [
		'ipLockFilterId',
		'reasonId',
		'status',
		'lockStatus',
		'method',
		'pattern',
		'title',
		'createdAt',
		'appliedAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'ipLockFilterId';

	protected array $indices		= [
		'reasonId',
		'status',
		'lockStatus',
		'method',
		'pattern',
		'title',
		'createdAt',
	];

	protected int $fetchMode		= PDO::FETCH_CLASS;

	protected ?string $className	= Entity_IP_Lock_Filter::class;
}
