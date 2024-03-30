<?php
/**
 *	Job Schedule Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2020 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Job Schedule Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2020 Ceus Media
 */
class Model_Job_Schedule extends Model
{
	public const STATUS_DISABLED		= 0;
	public const STATUS_ENABLED			= 1;
	public const STATUS_PAUSED			= 2;

	public const STATUSES				= [
		self::STATUS_DISABLED,
		self::STATUS_ENABLED,
		self::STATUS_PAUSED,
	];

	public const REPORT_MODE_NEVER		= 0;
	public const REPORT_MODE_ALWAYS		= 1;
	public const REPORT_MODE_CHANGE		= 2;
	public const REPORT_MODE_FAIL		= 3;
	public const REPORT_MODE_DONE		= 4;
	public const REPORT_MODE_SUCCESS	= 5;

	public const REPORT_MODES			= [
		self::REPORT_MODE_NEVER,
		self::REPORT_MODE_ALWAYS,
		self::REPORT_MODE_CHANGE,
		self::REPORT_MODE_FAIL,
		self::REPORT_MODE_DONE,
		self::REPORT_MODE_SUCCESS,
	];

	public const REPORT_CHANNEL_NONE	= 0;
	public const REPORT_CHANNEL_MAIL	= 1;
	public const REPORT_CHANNEL_XMPP	= 2;

	public const REPORT_CHANNELS		= [
		self::REPORT_CHANNEL_NONE,
		self::REPORT_CHANNEL_MAIL,
		self::REPORT_CHANNEL_XMPP,
	];

	public const TYPE_UNKNOWN			= 0;
	public const TYPE_CRON				= 1;
	public const TYPE_INTERVAL			= 2;
	public const TYPE_DATETIME			= 3;

	public const TYPES					= [
		self::TYPE_CRON,
		self::TYPE_INTERVAL,
		self::TYPE_DATETIME,
	];

	protected string $name			= 'job_schedule';

	protected array $columns		= [
		'jobScheduleId',
		'jobDefinitionId',
		'type',
		'status',
		'expression',
		'reportMode',
		'reportChannel',
		'reportReceivers',
		'arguments',
		'title',
		'createdAt',
		'modifiedAt',
		'lastRunAt',
	];

	protected string $primaryKey	= 'jobScheduleId';

	protected array $indices		= [
		'jobDefinitionId',
		'type',
		'status',
		'reportMode',
		'reportChannel',
		'createdAt',
		'modifiedAt',
		'lastRunAt',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
