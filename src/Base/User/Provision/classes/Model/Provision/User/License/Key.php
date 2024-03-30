<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Provision_User_License_Key extends Model
{
	public const STATUS_NEW			= 0;																	//  @de: nicht vergeben
	public const STATUS_ASSIGNED	= 1;																	//  @de: ergeben / zugewiesen
	public const STATUS_EXPIRED		= 2;																	//  @de: ergeben / zugewiesen

	protected string $name			= 'user_provision_license_keys';

	protected array $columns		= [
		'userLicenseKeyId',
		'userLicenseId',
		'userId',
		'productLicenseId',
		'productId',
		'status',
		'uid',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'userLicenseKeyId';

	protected array $indices		= [
		'userLicenseId',
		'userId',
		'productLicenseId',
		'productId',
		'status',
		'uid',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
