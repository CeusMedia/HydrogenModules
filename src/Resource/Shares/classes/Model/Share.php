<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Share extends Model
{
	const ACCESS_PUBLIC			= 0;
	const ACCESS_CAPTCHA		= 1;
	const ACCESS_LOGIN			= 2;

	protected string $name				= 'shares';

	protected array $columns			= array(
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
	);

	protected array $indices			= array(
		'status',
		'access',
		'validity',
		'moduleId',
		'relationId',
		'path',
		'uuid',
	);

	protected string $primaryKey		= 'shareId';

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
