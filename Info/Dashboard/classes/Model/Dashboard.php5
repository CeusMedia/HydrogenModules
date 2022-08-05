<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Dashboard extends Model
{
	protected $name		= 'dashboards';

	protected $columns	= array(
		'dashboardId',
		'userId',
		'isCurrent',
		'title',
		'description',
		'panels',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'dashboardId';

	protected $indices		= array(
		'userId',
		'isCurrent',
		'title',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
