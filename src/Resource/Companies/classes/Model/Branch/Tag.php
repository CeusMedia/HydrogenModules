<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Branch_Tag extends Model
{
	protected string $name			= 'branch_tags';

	protected array $columns		= [
		'branchTagId',
		'branchId',
		'label',
		'createdAt',
	];

	protected string $primaryKey	= 'branchTagId';

	protected array $indices		= [
		'branchId',
		'label',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
