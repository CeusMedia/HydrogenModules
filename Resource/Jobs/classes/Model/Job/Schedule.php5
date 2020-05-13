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
		'createdAt',
		'modifiedAt',
		'lastRunAt',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
