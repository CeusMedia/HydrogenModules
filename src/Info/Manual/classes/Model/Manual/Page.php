<?php

use CeusMedia\HydrogenFramework\Model;

/**
 * @property Entity_Manual_Category $category
 */
class Model_Manual_Page extends Model
{
	public const FORMAT_TEXT		= 0;
	public const FORMAT_HTML		= 1;
	public const FORMAT_MARKDOWN	= 2;

	public const FORMATS			= [
		self::FORMAT_TEXT,
		self::FORMAT_HTML,
		self::FORMAT_MARKDOWN,
	];

	public const STATUS_ARCHIVED	= -9;
	public const STATUS_OUTDATED	= -1;
	public const STATUS_NEW			= 0;
	public const STATUS_CHANGED		= 1;
	public const STATUS_ACTIVE		= 2;
	public const STATUS_LOCKED		= 3;

	public const STATUSES			= [
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

	protected int $fetchMode		= PDO::FETCH_CLASS;

	protected ?string $className	= 'Entity_Manual_Page';
}
