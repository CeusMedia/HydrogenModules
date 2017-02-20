<?php
class Model_Dashboard extends CMF_Hydrogen_Model{

	protected $name		= 'dashboards';
	protected $columns	= array(
		'dashboardId',
		'userId',
		'isCurrent',
		'title',
		'panels',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'dashboardId';
	protected $indices		= array(
		'userId',
		'isCurrent',
		'title',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
