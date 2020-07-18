<?php
/**
 *	Job Run Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2020 Ceus Media
 */
/**
 *	Job Run Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2020 Ceus Media
 */
class Model_Job_Run extends CMF_Hydrogen_Model
{
	const STATUS_TERMINATED		= -3;
	const STATUS_FAILED			= -2;
	const STATUS_ABORTED		= -1;
	const STATUS_PREPARED		= 0;
	const STATUS_RUNNING		= 1;
	const STATUS_DONE			= 2;
	const STATUS_SUCCESS		= 3;

	const STATUSES				= array(
		self::STATUS_TERMINATED,
		self::STATUS_FAILED,
		self::STATUS_ABORTED,
		self::STATUS_PREPARED,
		self::STATUS_RUNNING,
		self::STATUS_DONE,
		self::STATUS_SUCCESS,
	);

	const STATUS_TRANSITIONS	= array(
		self::STATUS_TERMINATED		=> array(
			self::STATUS_PREPARED,
		),
		self::STATUS_FAILED			=> array(
		),
		self::STATUS_ABORTED		=> array(
			self::STATUS_PREPARED,
		),
		self::STATUS_PREPARED		=> array(
			self::STATUS_RUNNING,
		),
		self::STATUS_RUNNING		=> array(
			self::STATUS_TERMINATED,
			self::STATUS_FAILED,
			self::STATUS_DONE,
			self::STATUS_SUCCESS,
		),
		self::STATUS_DONE		=> array(
			self::STATUS_SUCCESS,
		),
	);

	const TYPE_MANUALLY			= 0;
	const TYPE_SCHEDULED		= 1;

	const TYPES					= array(
		self::TYPE_MANUALLY,
		self::TYPE_SCHEDULED,
	);

	const REPORT_MODE_NEVER		= 0;
	const REPORT_MODE_ALWAYS	= 1;
	const REPORT_MODE_CHANGE	= 2;
	const REPORT_MODE_FAIL		= 3;
	const REPORT_MODE_DONE		= 4;
	const REPORT_MODE_SUCCESS	= 5;

	const REPORT_MODES			= array(
		self::REPORT_MODE_NEVER,
		self::REPORT_MODE_ALWAYS,
		self::REPORT_MODE_CHANGE,
		self::REPORT_MODE_FAIL,
		self::REPORT_MODE_DONE,
		self::REPORT_MODE_SUCCESS,
	);

	const REPORT_CHANNEL_NONE	= 0;
	const REPORT_CHANNEL_MAIL	= 1;
	const REPORT_CHANNEL_XMPP	= 2;

	const REPORT_CHANNELS		= array(
		self::REPORT_CHANNEL_NONE,
		self::REPORT_CHANNEL_MAIL,
		self::REPORT_CHANNEL_XMPP,
	);

	const ARCHIVED_NO			= 0;
	const ARCHIVED_YES			= 1;

	protected $name			= 'job_runs';
	protected $columns		= array(
		'jobRunId',
		'jobDefinitionId',
		'jobScheduleId',
		'processId',
		'type',
		'status',
		'archived',
		'reportMode',
		'reportChannel',
		'reportReceivers',
		'arguments',
		'title',
		'message',
		'createdAt',
		'modifiedAt',
		'ranAt',
		'finishedAt',
	);
	protected $primaryKey	= 'jobRunId';
	protected $indices		= array(
		'jobDefinitionId',
		'jobScheduleId',
		'processId',
		'type',
		'status',
		'archived',
		'reportMode',
		'reportChannel',
		'createdAt',
		'modifiedAt',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
