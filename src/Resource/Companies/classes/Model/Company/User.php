<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Company_User extends Model
{
	protected string $name			= 'company_users';

	protected array $columns		= [
		'companyUserId',
		'companyId',
		'userId',
//		'status',
//		'role',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'companyUserId';

	protected array $indices		= [
		'companyId',
		'userId',
//		'status',
//		'role',
	];

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
