<?php
class Model_Provision_User_License_Key extends CMF_Hydrogen_Model{

	const STATUS_NEW		= 0;																	//  @de: nicht vergeben
	const STATUS_ASSIGNED	= 1;																	//  @de: ergeben / zugewiesen

	protected $name			= 'provision_user_license_keys';

	protected $columns		= array(
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

	protected $primaryKey	= 'userLicenseKeyId';

	protected $indices		= array(
		'userLicenseId',
		'userId',
		'productLicenseId',
		'productId',
		'status',
		'uid',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
