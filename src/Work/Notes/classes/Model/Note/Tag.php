<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Note_Tag extends Model
{
	public const STATUS_DISABLED	= -1;
	public const STATUS_NORMAL		= 0;

	public const STATUSES			= [
		self::STATUS_DISABLED,
		self::STATUS_NORMAL,
	];

	protected string $name			= 'note_tags';

	protected array $columns		= [
		'noteTagId',
		'noteId',
		'status',
		'tagId',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'noteTagId';

	protected array $indices		= [
		'noteId',
		'tagId',
		'status',
		'createdAt',
		'modifiedAt',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
