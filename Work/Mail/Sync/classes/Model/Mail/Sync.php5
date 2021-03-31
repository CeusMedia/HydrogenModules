<?php
class Model_Mail_Sync extends CMF_Hydrogen_Model{

	const STATUS_ERROR			= -1;
	const STATUS_NEW			= 0;
	const STATUS_ACTIVE			= 1;
	const STATUS_SYNCHING		= 2;
	const STATUS_SYNCHED		= 3;
	const STATUS_CLOSED			= 4;

	const STATUSES				= [
		self::STATUS_ERROR,
		self::STATUS_NEW,
		self::STATUS_ACTIVE,
		self::STATUS_SYNCHING,
		self::STATUS_SYNCHED,
		self::STATUS_CLOSED,
	];

	protected $name		= 'mail_syncs';

	protected $columns	= array(
		'mailSyncId',
		'sourceMailHostId',
		'targetMailHostId',
		'status',
		'resync',
		'sourceUsername',
		'targetUsername',
		'sourcePassword',
		'targetPassword',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'mailSyncId';

	protected $indices		= array(
		'sourceMailHostId',
		'targetMailHostId',
		'sourceUsername',
		'targetUsername',
		'status',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
