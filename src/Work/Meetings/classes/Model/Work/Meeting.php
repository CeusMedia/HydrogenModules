<?php
class Model_Work_Meeting extends CeusMedia\HydrogenFramework\Model\Database\Table
{
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

	protected string $name				= 'work_meetings';
	protected array $columns			= [
		'workMeetingId',
		'creatorId',
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
	protected array $indexes			= [
		'creatorId',
		'status',
		'startsAt',
		'minutes',
		'location',
		'title',
		'link',
	];
	protected string $primaryKey		= 'workMeetingId';

	protected int $fetchMode			= PDO::FETCH_CLASS;
	protected ?string $fetchEntityClass	= Entity_Work_Meeting::class;
}