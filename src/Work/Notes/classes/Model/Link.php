<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Link extends Model
{
	protected string $name			= 'links';

	protected array $columns		= [
		'linkId',
		'url',
		'createdAt',
		'lastAssignAt',
		'lastSearchAt',
	];

	protected string $primaryKey	= 'linkId';

	protected array $indices		= [
		'url'
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
