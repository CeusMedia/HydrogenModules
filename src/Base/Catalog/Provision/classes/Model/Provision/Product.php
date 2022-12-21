<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Provision_Product extends Model
{
	const STATUS_DEACTIVATED	= -1;
	const STATUS_NEW			= 0;
	const STATUS_ACTIVE			= 1;
	const STATUS_EXPIRED		= 2;

	protected string $name			= 'provision_products';

	protected array $columns		= array(
		'productId',
		'status',
		'rank',
		'title',
		'url',
		'description',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'productId';

	protected array $indices		= array(
		'status',
		'rank',
		'title',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
