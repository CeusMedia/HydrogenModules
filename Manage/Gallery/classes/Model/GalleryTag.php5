<?php

use CeusMedia\HydrogenFramework\Model;

class Model_GalleryTag extends Model
{
	protected $name		= 'gallery_tags';

	protected $columns	= array(
		'galleryTagId',
		'galleryId',
		'tagId'
	);

	protected $primaryKey	= 'galleryTagId';

	protected $indices		= array(
		'galleryId',
		'tagId'
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
