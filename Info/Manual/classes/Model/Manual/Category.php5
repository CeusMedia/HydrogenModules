<?php
class Model_Manual_Category extends CMF_Hydrogen_Model
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

	protected $name			= 'manual_categories';

	protected $columns		= [
		'manualCategoryId',
		'creatorId',
		'status',
		'format',
		'version',
		'rank',
		'title',
		'content',
		'createdAt',
		'modifiedAt',
	];

	protected $primaryKey	= 'manualCategoryId';

	protected $indices		= [
		'creatorId',
		'status',
		'format',
		'title',
	];

	protected $fetchMode	= PDO::FETCH_OBJ;
}
