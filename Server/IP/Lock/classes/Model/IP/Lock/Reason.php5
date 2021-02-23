<?php
class Model_IP_Lock_Reason extends CMF_Hydrogen_Model
{
	const STATUS_DISABLED	= 0;
	const STATUS_ENABLED	= 1;

	const STATUSES			= [
		self::STATUS_DISABLED,
		self::STATUS_ENABLED,
	];

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
