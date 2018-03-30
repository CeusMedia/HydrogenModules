<?php
class Model_Provision_User_License extends CMF_Hydrogen_Model{

	const STATUS_DEACTIVATED	= -2;
	const STATUS_REVOKED		= -1;
	const STATUS_NEW			= 0;
	const STATUS_ACTIVE			= 1;
	const STATUS_EXPIRED		= 2;

	protected $name			= 'provision_user_licenses';

	protected $columns		= array(
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
	);

	protected $primaryKey	= 'userLicenseId';

	protected $indices		= array(
		'userId',
		'productLicenseId',
		'productId',
		'status',
		'uid',
		'startsAt',
		'endsAt',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
