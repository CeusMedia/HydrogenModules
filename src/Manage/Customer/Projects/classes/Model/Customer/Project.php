<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Customer_Project extends Model
{
	protected string $name			= 'customer_projects';

	protected array $columns		= array(
		'customerProjectId',
		'customerId',
		'projectId',
		'userId',
		'status',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'customerProjectId';

	protected array $indices		= array(
		'customerId',
		'projectId',
		'userId',
		'status'
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
