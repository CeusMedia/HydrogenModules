<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Company extends Model
{
	public const STATUS_INACTIVE	= -2;
	public const STATUS_REJECTED	= -1;
	public const STATUS_NEW			= 0;
	public const STATUS_CHANGED		= 1;
	public const STATUS_ACTIVE		= 2;

	protected string $name			= 'companies';

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'companyId';

	protected array $indices		= [
		'status',
		'title',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
