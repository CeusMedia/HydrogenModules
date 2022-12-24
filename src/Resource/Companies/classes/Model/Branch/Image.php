<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Branch_Image extends Model
{
	protected string $name			= 'branch_images';

	protected array $columns		= [
		'branchImageId',
		'branchId',
		'type',
		'filename',
		'title',
		'uploadedAt',
	];

	protected string $primaryKey	= 'branchImageId';

	protected array $indices		= [
		'branchId',
		'type',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
