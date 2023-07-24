<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Provision_User_License extends Model
{
	const STATUS_DEACTIVATED	= -2;
	const STATUS_REVOKED		= -1;
	const STATUS_NEW			= 0;
	const STATUS_ACTIVE			= 1;
	const STATUS_EXPIRED		= 2;

	protected string $name			= 'user_provision_licenses';

	protected array $columns		= [
		'userLicenseId',
		'userId',
		'productLicenseId',
		'productId',
		'status',
		'uid',
		'duration',
		'users',
		'price',
		'currency',
		'createdAt',
		'modifiedAt',
		'startsAt',
		'endsAt',
	];

	protected string $primaryKey	= 'userLicenseId';

	protected array $indices		= [
		'userId',
		'productLicenseId',
		'productId',
		'status',
		'uid',
		'startsAt',
		'endsAt',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
