<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Company extends Model
{
	const STATUS_INACTIVE	= -2;
	const STATUS_REJECTED	= -1;
	const STATUS_NEW		= 0;
	const STATUS_CHANGED	= 1;
	const STATUS_ACTIVE		= 2;

	protected string $name			= 'companies';

	protected array $columns		= array(
		'companyId',
		'status',
		'title',
		'description',
		'sector',
		'postcode',
		'city',
		'street',
		'number',
		'phone',
		'fax',
		'url',
		'logo',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'companyId';

	protected array $indices		= array(
		'status',
		'title',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
