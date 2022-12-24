<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Company extends Model
{
	protected string $name			= 'companies';

	protected array $columns		= [
		'companyId',
		'status',
		'title',
		'sector',
		'postcode',
		'city',
		'street',
		'number',
		'phone',
		'fax',
		'url',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'companyId';

	protected array $indices		= [
		'status',
		'title',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
