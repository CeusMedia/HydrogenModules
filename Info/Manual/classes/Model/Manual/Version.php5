<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Manual_Version extends Model
{
	const TYPE_PAGE			= 0;
	const TYPE_CATEGORY		= 1;

	const TYPES				= [
		self::TYPE_PAGE,
		self::TYPE_CATEGORY,
	];

	protected string $name			= 'manual_versions';

	protected array $columns		= [
		'manualVersionId',
		'userId',
		'objectId',
		'type',
		'version',
		'object',
		'timestamp',
	];

	protected string $primaryKey	= 'manualVersionId';

	protected array $indices		= [
		'userId',
		'objectId',
		'type',
		'version',
	];

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
