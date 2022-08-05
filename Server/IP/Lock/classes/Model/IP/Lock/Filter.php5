<?php
use CeusMedia\HydrogenFramework\Model;

class Model_IP_Lock_Filter extends Model
{
	const STATUS_DISABLED	= 0;
	const STATUS_ENABLED	= 1;

	const STATUSES			= [
		self::STATUS_DISABLED,
		self::STATUS_ENABLED,
	];

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
		'pattern',
		'title',
		'createdAt',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
