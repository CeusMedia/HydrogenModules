<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Link extends Model
{
	protected string $name		= 'links';

	protected array $columns	= array(
		'linkId',
		'url',
		'createdAt',
		'lastAssignAt',
		'lastSearchAt',
	);

	protected string $primaryKey	= 'linkId';

	protected array $indices		= array(
		'url'
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
