<?php

use CeusMedia\HydrogenFramework\Model;

class Model_GalleryAuthor extends Model
{
	protected $name		= 'gallery_authors';

	protected $columns	= array(
		'galleryAuthorId',
		'galleryId',
		'userId'
	);

	protected $primaryKey	= 'galleryAuthorId';

	protected $indices		= array(
		'galleryId',
		'userId'
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
