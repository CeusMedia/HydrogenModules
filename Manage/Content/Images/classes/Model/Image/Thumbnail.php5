<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Image_Thumbnail extends Model
{
	protected $name		= 'image_thumbnails';

	protected $columns	= array(
		'imageThumbnailId',
		'imageId',
		'maxWidth',
		'maxHeight',
		'realWidth',
		'realHeight',
		'data',
		'timestamp',
	);

	protected $primaryKey	= 'imageThumbnailId';

	protected $indices		= array(
		'imageId',
		'maxWidth',
		'maxHeight',
		'realWidth',
		'realHeight',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
