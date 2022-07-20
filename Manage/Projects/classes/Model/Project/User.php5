<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Project_User extends Model
{
	protected $name			= 'project_users';

	protected $columns		= array(
		'projectUserId',
		'projectId',
		'userId',
		'isDefault',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'projectUserId';

	protected $indices		= array(
		'projectId',
		'userId',
		'isDefault',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
