<?php
use CeusMedia\HydrogenFramework\Model;

class Model_Finance_Bank extends Model
{
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
