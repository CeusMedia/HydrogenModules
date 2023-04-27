<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Finance_Bank_Account extends Model
{
	protected string $name		= 'finance_bank_accounts';

	protected array $columns	= [
		'bankAccountId',
		'bankId',
		'type',
		'scope',
		'currency',
		'accountKey',
		'title',
		'fee',
		'debit',
		'value',
		'timestamp',
	];

	protected string $primaryKey	= 'bankAccountId';

	protected array $indices		= [
		'bankId',
	];

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
