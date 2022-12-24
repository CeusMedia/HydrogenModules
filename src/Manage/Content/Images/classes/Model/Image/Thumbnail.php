<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Image_Thumbnail extends Model
{
	protected string $name			= 'image_thumbnails';

	protected array $columns		= [
		'imageThumbnailId',
		'imageId',
		'maxWidth',
		'maxHeight',
		'realWidth',
		'realHeight',
		'data',
		'timestamp',
	];

	protected string $primaryKey	= 'imageThumbnailId';

	protected array $indices		= [
		'imageId',
		'maxWidth',
		'maxHeight',
		'realWidth',
		'realHeight',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
