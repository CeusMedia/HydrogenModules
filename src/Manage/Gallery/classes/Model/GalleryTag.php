<?php

use CeusMedia\HydrogenFramework\Model;

class Model_GalleryTag extends Model
{
	protected string $name			= 'gallery_tags';

	protected array $columns		= [
		'galleryTagId',
		'galleryId',
		'tagId'
	];

	protected string $primaryKey	= 'galleryTagId';

	protected array $indices		= [
		'galleryId',
		'tagId'
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
