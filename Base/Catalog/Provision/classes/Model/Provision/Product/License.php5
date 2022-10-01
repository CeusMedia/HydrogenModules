<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Provision_Product_License extends Model
{
	protected string $name			= 'provision_product_licenses';

	protected array $columns		= array(
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

	protected string $primaryKey	= 'productLicenseId';

	protected array $indices		= array(
		'productId',
		'status',
		'rank',
		'title',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
