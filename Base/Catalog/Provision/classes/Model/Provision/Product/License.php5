<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Provision_Product_License extends Model
{
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
