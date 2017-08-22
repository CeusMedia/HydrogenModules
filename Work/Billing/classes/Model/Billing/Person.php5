<?php
class Model_Billing_Person extends CMF_Hydrogen_Model{

	const STATUS_DISABLED	= -1;
	const STATUS_NEW		= 0;

	protected $name		= "billing_persons";
	protected $columns	= array(
		'personId',
		'status',
		'email',
		'firstname',
		'surname',
		'balance',
	);
	protected $primaryKey	= 'personId';
	protected $indices		= array(
		'status',
		'email',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
