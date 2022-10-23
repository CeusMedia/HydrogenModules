<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Finance_Fund extends Model
{
	protected string $name		= 'finance_funds';

	protected array $columns	= array(
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

	protected string $primaryKey	= 'fundId';

	protected array $indices		= array(
		'userId',
		'type',
		'scope',
		'ISIN',
		'currency',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
