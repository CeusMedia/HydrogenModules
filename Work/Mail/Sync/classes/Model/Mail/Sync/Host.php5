<?php
class Model_Mail_Sync_Host extends CMF_Hydrogen_Model{

	protected $name		= 'mail_sync_hosts';
	protected $columns	= array(
		'mailSyncHostId',
		'ip',
		'host',
		'port',
		'ssl',
		'auth',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'mailSyncHostId';
	protected $indices		= array(
		'ip',
		'host',
		'port',
		'ssl',
		'auth',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
