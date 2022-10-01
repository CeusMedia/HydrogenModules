<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Finance_FundPrice extends Model
{
	protected string $name		= 'finance_fund_prices';

	protected array $columns	= array(
		'fundPriceId',
		'fundId',
		'pieces',
		'price',
		'timestamp',
	);

	protected string $primaryKey	= 'fundPriceId';

	protected array $indices		= ['fundId'];

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
