<?php
class Model_Provision_Product_License extends CMF_Hydrogen_Model{

	protected $name			= 'provision_product_licenses';

	protected $columns		= array(
		'productLicenseId',
		'productId',
		'status',
		'rank',
		'title',
		'duration',
		'users',
		'price',
		'currency',
		'description',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'productLicenseId';

	protected $indices		= array(
		'productId',
		'status',
		'rank',
		'title',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
