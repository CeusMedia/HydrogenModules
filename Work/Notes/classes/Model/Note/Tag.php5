<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Note_Tag extends Model
{
	const STATUS_DISABLED	= -1;
	const STATUS_NORMAL		= 0;

	const STATUSES			= [
		self::STATUS_DISABLED,
		self::STATUS_NORMAL,
	];

	protected string $name		= 'note_tags';

	protected array $columns	= array(
		'noteTagId',
		'noteId',
		'status',
		'tagId',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'noteTagId';

	protected array $indices		= array(
		'noteId',
		'tagId',
		'status',
		'createdAt',
		'modifiedAt',
	 );

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
