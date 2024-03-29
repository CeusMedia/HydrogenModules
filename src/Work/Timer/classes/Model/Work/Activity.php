<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Work_Activity extends Model
{
	protected string $name			= 'work_activities';

	protected array $columns		= [
		'workActivityId',
		'userId',
		'title',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'workActivityId';

	protected array $indices		= [
		'userId',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
