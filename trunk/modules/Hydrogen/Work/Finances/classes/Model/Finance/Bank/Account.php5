<?php
class Model_Finance_Bank_Account extends CMF_Hydrogen_Model {

	protected $name		= 'finance_bank_accounts';
	protected $columns	= array(
		'bankAccountId',
		'bankId',
		'type',
		'currency',
		'accountKey',
		'title',
		'value',
		'timestamp',
	);
	protected $primaryKey	= 'bankAccountId';
	protected $indices		= array(
		'bankId',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>