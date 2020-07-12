<?php
/**
 *	Job Schedule Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2020 Ceus Media
 */
/**
 *	Job Schedule Model.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2020 Ceus Media
 */
class Model_Job_Schedule extends CMF_Hydrogen_Model
{
	const STATUS_DISABLED	= 0;
	const STATUS_ENABLED	= 1;

	const STATUSES				= array(
		self::STATUS_DISABLED,
		self::STATUS_ENABLED,
	);

	const REPORT_MODE_NEVER		= 0;
	const REPORT_MODE_ERROR		= 1;
	const REPORT_MODE_FAIL		= 2;
	const REPORT_MODE_NEGATIVE	= 3;
	const REPORT_MODE_DONE		= 4;
	const REPORT_MODE_WORKLOAD	= 8;
	const REPORT_MODE_POSITIVE	= 12;
	const REPORT_MODE_ALL		= 15;

	const REPORT_MODES			= array(
		self::REPORT_MODE_NEVER,
		self::REPORT_MODE_ERROR,
		self::REPORT_MODE_FAIL,
		self::REPORT_MODE_NEGATIVE,
		self::REPORT_MODE_DONE,
		self::REPORT_MODE_WORKLOAD,
		self::REPORT_MODE_POSITIVE,
		self::REPORT_MODE_ALL,
	);

	const REPORT_CHANNEL_NONE	= 0;
	const REPORT_CHANNEL_MAIL	= 1;
	const REPORT_CHANNEL_XMPP	= 2;

	const REPORT_CHANNELS		= array(
		self::REPORT_CHANNEL_NONE,
		self::REPORT_CHANNEL_MAIL,
		self::REPORT_CHANNEL_XMPP,
	);

	protected $name			= 'job_schedule';
	protected $columns		= array(
		'jobScheduleId',
		'jobDefinitionId',
		'status',
		'minuteOfHour',
		'hourOfDay',
		'dayOfWeek',
		'dayOfMonth',
		'monthOfYear',
		'reportMode',
		'reportChannel',
		'reportReceivers',
		'title',
		'createdAt',
		'modifiedAt',
		'lastRunAt',
	);
	protected $primaryKey	= 'jobScheduleId';
	protected $indices		= array(
		'jobDefinitionId',
		'status',
		'minuteOfHour',
		'hourOfDay',
		'dayOfWeek',
		'dayOfMonth',
		'monthOfYear',
		'reportMode',
		'reportChannel',
		'createdAt',
		'modifiedAt',
		'lastRunAt',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
