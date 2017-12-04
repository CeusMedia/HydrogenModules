<?php
class Model_Mail_Host extends CMF_Hydrogen_Model{

	protected $name		= 'mail_hosts';
	protected $columns	= array(
		'mailHostId',
		'ip',
		'host',
		'port',
		'ssl',
		'auth',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'mailHostId';
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
