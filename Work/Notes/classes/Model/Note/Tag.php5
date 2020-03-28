<?php
class Model_Note_Tag extends CMF_Hydrogen_Model{

	const STATUS_DISABLED	= -1;
	const STATUS_NORMAL		= 0;

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

