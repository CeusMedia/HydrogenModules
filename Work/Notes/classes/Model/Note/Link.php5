<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Note_Link extends Model
{
	protected $name		= 'note_links';

	protected $columns	= array(
		'noteLinkId',
		'noteId',
		'linkId',
		'title',
		'createdAt',
	);

	protected $primaryKey	= 'noteLinkId';

	protected $indices		= array(
		'noteId',
		'linkId'
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
