<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Image extends Model
{
	protected string $name			= 'images';

	protected array $columns		= [
		'imageId',
		'companyId',
		'branchId',
		'creditId',
		'status',
		'title',
//		'timestamp',
	];

	protected string $primaryKey	= 'imageId';

	protected array $indices		= [
		'companyId',
		'branchId',
		'creditId',
  		'status',
	];
}
