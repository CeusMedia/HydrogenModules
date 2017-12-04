<?php
class Model_Mail_Box extends CMF_Hydrogen_Model{

	protected $name		= 'mail_boxes';
	protected $columns	= array(
		'mailBoxId',
		'mailHostId',
		'username',
		'password',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'mailBoxId';
	protected $indices		= array(
		'mailHostId',
		'username',
		'password',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
