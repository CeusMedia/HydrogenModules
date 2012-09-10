<?php
class Model_Project extends CMF_Hydrogen_Model{
	protected $name			= 'projects';
	protected $columns		= array(
		'projectId',
		'parentId',
		'status',
		'url',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'projectId';
	protected $indices		= array(
		'parentId',
		'status',
		'title',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>