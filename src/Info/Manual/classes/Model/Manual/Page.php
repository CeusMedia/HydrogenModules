<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Manual_Page extends Model
{
	const FORMAT_TEXT		= 0;
	const FORMAT_HTML		= 1;
	const FORMAT_MARKDOWN	= 2;

	const FORMATS			= [
		self::FORMAT_TEXT,
		self::FORMAT_HTML,
		self::FORMAT_MARKDOWN,
	];

	const STATUS_ARCHIVED	= -9;
	const STATUS_OUTDATED	= -1;
	const STATUS_NEW		= 0;
	const STATUS_CHANGED	= 1;
	const STATUS_ACTIVE		= 2;
	const STATUS_LOCKED		= 3;

	const STATUSES			= [
		self::STATUS_ARCHIVED,
		self::STATUS_OUTDATED,
		self::STATUS_NEW,
		self::STATUS_CHANGED,
		self::STATUS_ACTIVE,
		self::STATUS_LOCKED,
	];

	protected string $name			= 'manual_pages';

	protected array $columns		= [
		'manualPageId',
		'manualCategoryId',
		'creatorId',
		'parentId',
		'status',
		'format',
		'version',
		'rank',
		'title',
		'content',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'manualPageId';

	protected array $indices		= [
		'manualCategoryId',
		'creatorId',
		'parentId',
		'status',
		'format',
		'title',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
