<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Test_Table extends Model
{
	protected string $name			= 'test_table';

	protected array $columns		= array(
		'testId',
		'title',
		'timestamp',
	);

	protected string $primaryKey	= 'testId';

	protected array $indices		= [];
}
