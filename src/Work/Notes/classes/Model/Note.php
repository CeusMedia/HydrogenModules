<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Note extends Model
{
	protected string $name			= 'notes';

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'noteId';

	protected array $indices		= [
		'userId',
		'projectId',
		'status',
		'public',
		'format',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
