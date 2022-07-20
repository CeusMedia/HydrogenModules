<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Finance_Fund extends Model
{
	protected $name		= 'finance_funds';

	protected $columns	= array(
		'fundId',
		'userId',
		'type',
		'scope',
		'ISIN',
		'currency',
		'pieces',
		'kag',
		'title',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'fundId';

	protected $indices		= array(
		'userId',
		'type',
		'scope',
		'ISIN',
		'currency',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
