<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Provision_User_License_Key extends Model
{
	const STATUS_NEW		= 0;																	//  @de: nicht vergeben
	const STATUS_ASSIGNED	= 1;																	//  @de: ergeben / zugewiesen
	const STATUS_EXPIRED	= 2;																	//  @de: ergeben / zugewiesen

	protected string $name			= 'user_provision_license_keys';

	protected array $columns		= array(
		'userLicenseKeyId',
		'userLicenseId',
		'userId',
		'productLicenseId',
		'productId',
		'status',
		'uid',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'userLicenseKeyId';

	protected array $indices		= array(
		'userLicenseId',
		'userId',
		'productLicenseId',
		'productId',
		'status',
		'uid',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
?>
