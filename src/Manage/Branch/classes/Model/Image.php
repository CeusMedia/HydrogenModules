<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Image extends Model
{
	protected string $name			= 'images';

	protected array $columns		= array(
		'imageId',
		'companyId',
		'branchId',
		'creditId',
		'status',
		'title',
//		'timestamp',
	);

	protected string $primaryKey	= 'imageId';

	protected array $indices		= array(
		'companyId',
		'branchId',
		'creditId',
  		'status',
	);
}
