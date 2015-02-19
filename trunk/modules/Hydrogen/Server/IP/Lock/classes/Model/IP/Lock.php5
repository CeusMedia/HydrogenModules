<?php
class Model_IP_Lock extends CMF_Hydrogen_Model{

	const STATUS_UNLOCKED		= -2;
	const STATUS_CANCELLED		= -1;
	const STATUS_REQUEST_LOCK	= 0;
	const STATUS_LOCKED			= 1;
	const STATUS_REQUEST_UNLOCK	= 2;

	protected $name		= 'ip_locks';
	protected $columns	= array(
		'ipLockId',
		'reasonId',
		'status',
		'IPv4',
		'views',
		'lockedAt',
		'visitedAt',
		'unlockedAt',
	);
	protected $primaryKey	= 'ipLockId';
	protected $indices		= array(
		'reasonId',
		'status',
		'IPv4',
		'lockedAt',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
