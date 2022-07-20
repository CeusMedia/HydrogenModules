<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Test_Table extends Model
{
	protected $name			= 'test_table';

	protected $columns		= array(
		'testId',
		'title',
		'timestamp',
	);

	protected $primaryKey	= 'testId';

	protected $indices		= [];
}
