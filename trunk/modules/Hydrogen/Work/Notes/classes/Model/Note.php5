<?php
class Model_Note extends CMF_Hydrogen_Model{

	protected $name		= 'notes';
	protected $columns	= array(
		'noteId',
		'userId',
		'projectId',
		'status',
		'public',
		'title',
		'content',
		'numberViews',
		'createdAt',
		'modifiedAt'
	);
	protected $primaryKey	= 'noteId';
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
