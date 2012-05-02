<?php
class Model_Finance_Bank extends CMF_Hydrogen_Model {

	protected $name		= 'finance_banks';
	protected $columns	= array(
		'bankId',
		'userId',
		'type',
		'username',
		'password',
		'title',
		'createdAt',
		'modifiedAt',
		'updatedAt',
	);
	protected $primaryKey	= 'bankId';
	protected $indices		= array(
		'userId',
		'type',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>