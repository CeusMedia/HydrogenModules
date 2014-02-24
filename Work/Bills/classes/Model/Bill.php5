<?php
class Model_Bill extends CMF_Hydrogen_Model{
	protected $name			= 'bills';
	protected $columns		= array(
		'billId',
		'userId',
		'customerId',
		'type',
		'status',
		'price',
		'date',
		'title',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'billId';
	protected $indices		= array(
		'userId',
		'customerId',
		'type',
		'status',
		'date',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
