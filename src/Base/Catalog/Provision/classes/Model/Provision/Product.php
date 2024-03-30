<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Provision_Product extends Model
{
	public const STATUS_DEACTIVATED	= -1;
	public const STATUS_NEW			= 0;
	public const STATUS_ACTIVE		= 1;
	public const STATUS_EXPIRED		= 2;

	protected string $name			= 'provision_products';

	protected array $columns		= [
		'productId',
		'status',
		'rank',
		'title',
		'url',
		'description',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'productId';

	protected array $indices		= [
		'status',
		'rank',
		'title',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
