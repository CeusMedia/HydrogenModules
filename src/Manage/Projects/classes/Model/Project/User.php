<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Project_User extends Model
{
	protected string $name			= 'project_users';

	protected array $columns		= [
		'projectUserId',
		'projectId',
		'creatorId',
		'userId',
		'status',
		'isDefault',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'projectUserId';

	protected array $indices		= [
		'projectId',
		'userId',
		'status',
		'isDefault',
	];

	protected int $fetchMode		= PDO::FETCH_CLASS;

	protected ?string $className	= Entity_Project_User::class;
}
