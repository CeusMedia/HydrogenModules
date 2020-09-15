<?php
class Model_Work_Activity extends CMF_Hydrogen_Model{
	protected $name			= 'work_activities';
	protected $columns		= array(
		'workActivityId',
		'userId',
		'title',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'workActivityId';
	protected $indices		= array(
		'userId',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
