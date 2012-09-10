<?php
class Model_Project_User extends CMF_Hydrogen_Model{
	protected $name			= 'project_users';
	protected $columns		= array(
		'projectUserId',
		'projectId',
		'userId',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'projectUserId';
	protected $indices		= array(
		'projectId',
		'userId',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>