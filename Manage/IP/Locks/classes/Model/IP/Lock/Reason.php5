<?php
class Model_IP_Lock_Reason extends CMF_Hydrogen_Model{
	protected $name		= 'ip_lock_reasons';
	protected $columns	= array(
		'ipLockReasonId',
		'status',
		'duration',
		'title',
		'description',
		'createdAt',
		'appliedAt',
	);
	protected $primaryKey	= 'ipLockReasonId';
	protected $indices		= array(
		'status',
		'duration',
		'createdAt',
		'appliedAt',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
