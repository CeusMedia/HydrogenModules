<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Project_User extends Model
{
	protected string $name			= 'project_users';

	protected array $columns		= array(
		'projectUserId',
		'projectId',
		'userId',
		'isDefault',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'projectUserId';

	protected array $indices		= array(
		'projectId',
		'userId',
		'isDefault',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
