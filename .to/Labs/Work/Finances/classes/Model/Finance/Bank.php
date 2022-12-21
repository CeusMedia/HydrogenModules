<?php
use CeusMedia\HydrogenFramework\Model;

class Model_Finance_Bank extends Model
{
	protected string $name		= 'finance_banks';

	protected array $columns	= array(
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

	protected string $primaryKey	= 'bankId';

	protected array $indices		= array(
		'userId',
		'type',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
