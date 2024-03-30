<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Share extends Model
{
	public const ACCESS_PUBLIC			= 0;
	public const ACCESS_CAPTCHA			= 1;
	public const ACCESS_LOGIN			= 2;

	protected string $name				= 'shares';

	protected array $columns			= [
		'shareId',
		'status',
		'access',
		'validity',
		'moduleId',
		'relationId',
		'path',
		'uuid',
		'createdAt',
		'accessedAt',
	];

	protected array $indices			= [
		'status',
		'access',
		'validity',
		'moduleId',
		'relationId',
		'path',
		'uuid',
	];

	protected string $primaryKey		= 'shareId';

	protected int $fetchMode			= PDO::FETCH_OBJ;
}
