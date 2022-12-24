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
	const PRIORITY_NONE			= 0;
	const PRIORITY_HIGHEST		= 1;
	const PRIORITY_HIGH			= 2;
	const PRIORITY_NORMAL		= 3;
	const PRIORITY_LOW			= 4;
	const PRIORITY_LOWEST		= 5;

	const PRIORITIES			= [
		self::PRIORITY_NONE,
		self::PRIORITY_HIGHEST,
		self::PRIORITY_HIGH,
		self::PRIORITY_NORMAL,
		self::PRIORITY_LOW,
		self::PRIORITY_LOWEST,
	];

	const STATUS_ABORTED		= -2;
	const STATUS_REJECTED		= -1;
	const STATUS_NEW			= 0;
	const STATUS_ACCEPTED		= 1;
	const STATUS_PROGRESS		= 2;
	const STATUS_READY			= 3;
	const STATUS_FINISHED		= 4;

	const STATUSES				= [
		self::STATUS_ABORTED,
		self::STATUS_REJECTED,
		self::STATUS_NEW,
		self::STATUS_ACCEPTED,
		self::STATUS_PROGRESS,
		self::STATUS_READY,
		self::STATUS_FINISHED,
	];

	const TYPE_TASK				= 0;
	const TYPE_EVENT			= 1;

	const TYPES					= [
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
