<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Customer_Project extends Model
{
	protected $name			= 'customer_projects';

	protected $columns		= array(
		'customerProjectId',
		'customerId',
		'projectId',
		'userId',
		'status',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'customerProjectId';

	protected $indices		= array(
		'customerId',
		'projectId',
		'userId',
		'status'
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
