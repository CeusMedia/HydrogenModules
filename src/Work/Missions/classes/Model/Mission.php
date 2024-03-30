<?php
/**
 *	Model for missions.
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Model for missions.
 *	@todo			code documentation
 */
class Model_Mission extends Model
{
	public const PRIORITY_NONE			= 0;
	public const PRIORITY_HIGHEST		= 1;
	public const PRIORITY_HIGH			= 2;
	public const PRIORITY_NORMAL		= 3;
	public const PRIORITY_LOW			= 4;
	public const PRIORITY_LOWEST		= 5;

	public const PRIORITIES				= [
		self::PRIORITY_NONE,
		self::PRIORITY_HIGHEST,
		self::PRIORITY_HIGH,
		self::PRIORITY_NORMAL,
		self::PRIORITY_LOW,
		self::PRIORITY_LOWEST,
	];

	public const STATUS_ABORTED			= -2;
	public const STATUS_REJECTED		= -1;
	public const STATUS_NEW				= 0;
	public const STATUS_ACCEPTED		= 1;
	public const STATUS_PROGRESS		= 2;
	public const STATUS_READY			= 3;
	public const STATUS_FINISHED		= 4;

	public const STATUSES				= [
		self::STATUS_ABORTED,
		self::STATUS_REJECTED,
		self::STATUS_NEW,
		self::STATUS_ACCEPTED,
		self::STATUS_PROGRESS,
		self::STATUS_READY,
		self::STATUS_FINISHED,
	];

	public const TYPE_TASK				= 0;
	public const TYPE_EVENT				= 1;

	public const TYPES					= [
		self::TYPE_TASK.
		self::TYPE_EVENT,
	];

	/**	@var	$name		string		Table name without prefix of database connection */
	protected string $name				= "missions";

	/**	@var	array		$columns	List of columns within table */
	protected array $columns			= [
		'missionId',
		'creatorId',
		'modifierId',
		'workerId',
		'projectId',
		'type',
		'priority',
		'status',
		'dayStart',
		'dayEnd',
		'timeStart',
		'timeEnd',
		'minutesProjected',
		'minutesRequired',
		'title',
		'content',
		'location',
		'format',
		'reference',
		'createdAt',
		'modifiedAt',
	];

	/**	@var	string		$primaryKey		Name of column with primary key */
	protected string $primaryKey			= "missionId";

	/**	@var	array		$name			List of columns which are a foreign key and/or indexed */
	protected array $indices				= [
		'creatorId',
		'modifierId',
		'workerId',
		'projectId',
		'type',
		'priority',
		'status',
		'dayStart',
		'dayEnd',
	];

	/**	@var	integer		$fetchMode		Fetch mode, see PDO documentation */
	protected int $fetchMode				= PDO::FETCH_OBJ;
}
