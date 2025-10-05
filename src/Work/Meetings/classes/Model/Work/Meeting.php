<?php
class Model_Work_Meeting extends CeusMedia\HydrogenFramework\Model\Database\Table
{
	public const TYPE_GENERAL		= 0;

	public const PRIORITY_LOWEST	= 0;
	public const PRIORITY_LOW		= 1;
	public const PRIORITY_NORMAL	= 2;
	public const PRIORITY_HIGH		= 3;
	public const PRIORITY_HIGHEST	= 4;

	public const PRIORITIES			= [
		self::PRIORITY_LOWEST,
		self::PRIORITY_LOW,
		self::PRIORITY_NORMAL,
		self::PRIORITY_HIGH,
		self::PRIORITY_HIGHEST,
	];

	public const STATUS_CANCELLED	= -1;
	public const STATUS_NEW			= 0;
	public const STATUS_ACTIVE		= 1;
	public const STATUS_OUTDATED	= 2;
	public const STATUS_DONE		= 3;

	public const STATUSES			= [
		self::STATUS_CANCELLED,
		self::STATUS_NEW,
		self::STATUS_ACTIVE,
		self::STATUS_OUTDATED,
		self::STATUS_DONE,
	];

	protected string $name			= 'meetings';
	protected array $columns		= [
		'meetingId',
		'creatorId',
		'jobScheduleId',
		'type',
		'priority',
		'status',
		'dateStart',
		'dateEnd',
		'location',
		'title',
		'content',
		'link',
		'createdAt',
		'modifiedAt',
	];
	protected array $indices		= [
		'creatorId',
		'jobScheduleId',
		'type',
		'priority',
		'status',
		'dateStart',
		'dateEnd',
		'location',
		'title',
		'link',
	];
	protected string $primaryKey	= 'meetingId';

	protected int $fetchMode		= PDO::FETCH_CLASS;
	protected ?string $className	= Entity_Work_Meeting::class;
}
