<?php
class Model_Billing_Corporation extends CMF_Hydrogen_Model{

	const STATUS_DISABLED	= -1;
	const STATUS_NEW		= 0;

	protected $name		= "billing_corporations";
	protected $columns	= array(
		'corporationId',
		'status',
		'title',
		'balance',
		'iban',
		'bic',
	);
	protected $primaryKey	= 'corporationId';
	protected $indices		= array(
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
