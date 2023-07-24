<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Note_Link extends Model
{
	protected string $name			= 'note_links';

	protected array $columns		= [
		'noteLinkId',
		'noteId',
		'linkId',
		'title',
		'createdAt',
	];

	protected string $primaryKey	= 'noteLinkId';

	protected array $indices		= [
		'noteId',
		'linkId'
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
