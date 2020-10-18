<?php
class Model_Work_Timer extends CMF_Hydrogen_Model
{
	protected $name			= 'work_timers';

	protected $columns		= array(
		'workTimerId',
		'userId',
		'projectId',
		'workerId',
		'module',
		'moduleId',
		'status',
		'secondsPlanned',
		'secondsNeeded',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'workTimerId';

	protected $indices		= array(
		'userId',
		'projectId',
		'workerId',
		'module',
		'moduleId',
		'userId',
		'status',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
