<?php
class Model_IP_Lock_Filter extends CMF_Hydrogen_Model{
	protected $name		= 'ip_lock_filters';
	protected $columns	= array(
		'ipLockFilterId',
		'reasonId',
		'status',
		'lockStatus',
		'method',
		'pattern',
		'title',
		'createdAt',
		'appliedAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'ipLockFilterId';
	protected $indices		= array(
		'reasonId',
		'status',
		'lockStatus',
		'method',
		'title',
		'createdAt',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
