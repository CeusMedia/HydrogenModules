<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Dashboard extends Model
{
	protected string $name		= 'dashboards';

	protected array $columns	= array(
		'dashboardId',
		'userId',
		'isCurrent',
		'title',
		'description',
		'panels',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'dashboardId';

	protected array $indices		= array(
		'userId',
		'isCurrent',
		'title',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
