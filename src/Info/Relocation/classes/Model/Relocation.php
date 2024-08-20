<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Relocation extends Model
{
	protected string $name			= 'relocations';

	protected array $columns		= [
		'relocationId',
		'creatorId',
		'status',
		'title',
		'url',
		'views',
		'createdAt',
		'usedAt',
	];

	protected string $primaryKey	= 'relocationId';

	protected array $indices		= [
		'status',
		'url',
		'createdAt',
		'usedAt',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
