<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Note extends Model
{
	protected $name		= 'notes';

	protected $columns	= array(
		'noteId',
		'userId',
		'projectId',
		'status',
		'public',
		'format',
		'title',
		'content',
		'numberViews',
		'createdAt',
		'modifiedAt'
	);

	protected $primaryKey	= 'noteId';

	protected $indices		= array(
		'userId',
		'projectId',
		'status',
		'public',
		'format',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
