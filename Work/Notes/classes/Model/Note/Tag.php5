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

	protected $name		= 'note_tags';

	protected $columns	= array(
		'noteTagId',
		'noteId',
		'status',
		'tagId',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'noteTagId';

	protected $indices		= array(
		'noteId',
		'tagId',
		'status',
		'createdAt',
		'modifiedAt',
	 );

	protected $fetchMode	= PDO::FETCH_OBJ;
}
