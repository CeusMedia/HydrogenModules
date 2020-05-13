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
	const STATUS_TERMINATED	= -3;
	const STATUS_FAILED		= -2;
	const STATUS_ABORTED	= -1;
	const STATUS_PREPARED	= 0;
	const STATUS_RUNNING	= 1;
	const STATUS_DONE		= 2;

	const TYPE_MANUALLY		= 0;
	const TYPE_SCHEDULED	= 1;

	protected $name			= 'job_runs';
	protected $columns		= array(
		'jobRunId',
		'jobDefinitionId',
		'jobScheduleId',
		'processId',
		'type',
		'status',
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
		'createdAt',
		'modifiedAt',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
