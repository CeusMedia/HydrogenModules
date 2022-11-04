<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Branch_Tag extends Model
{
	protected string $name			= 'branch_tags';

	protected array $columns		= array(
		'branchTagId',
		'branchId',
		'label',
		'createdAt',
	);

	protected string $primaryKey	= 'branchTagId';

	protected array $indices		= array(
		'branchId',
		'label',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
