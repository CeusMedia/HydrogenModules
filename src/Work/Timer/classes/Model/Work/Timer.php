<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Work_Timer extends Model
{
	protected string $name			= 'work_timers';

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'workTimerId';

	protected array $indices		= [
		'userId',
		'projectId',
		'workerId',
		'module',
		'moduleId',
		'userId',
		'status',
	];

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
