<?php
use CeusMedia\HydrogenFramework\Model;

class Model_Branch_Image extends Model
{
	protected string $name			= 'branch_images';

	protected array $columns		= array(
		'imageId',
		'branchId',
		'filename',
		'title',
		'uploadedAt',
	);

	protected string $primaryKey	= 'imageId';

	protected array $indices		= array(
		'branchId',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
