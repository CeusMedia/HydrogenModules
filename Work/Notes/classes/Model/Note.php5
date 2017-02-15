<?php
class Model_Note extends CMF_Hydrogen_Model{

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
?>
