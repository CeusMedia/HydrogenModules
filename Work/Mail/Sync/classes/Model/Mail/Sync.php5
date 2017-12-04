<?php
class Model_Mail_Sync extends CMF_Hydrogen_Model{

	const STATUS_NEW			= 0;
	const STATUS_SYNCHED		= 1;
	const STATUS_RESYNCHED		= 2;
	const STATUS_CLOSED			= 3;

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
?>
