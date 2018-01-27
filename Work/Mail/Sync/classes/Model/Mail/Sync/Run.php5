<?php
class Model_Mail_Sync_Run extends CMF_Hydrogen_Model{

	const STATUS_FAIL			= -1;
	const STATUS_NEW			= 0;
	const STATUS_SUCCESS		= 1;

	protected $name		= 'mail_sync_runs';
	protected $columns	= array(
		'mailSyncRunId',
		'mailSyncId',
		'status',
		'message',
		'statistics',
		'output',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'mailSyncRunId';
	protected $indices		= array(
		'mailSyncId',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
