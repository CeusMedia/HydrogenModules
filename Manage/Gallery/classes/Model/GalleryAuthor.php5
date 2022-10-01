<?php

use CeusMedia\HydrogenFramework\Model;

class Model_GalleryAuthor extends Model
{
	protected string $name		= 'gallery_authors';

	protected array $columns	= array(
		'galleryAuthorId',
		'galleryId',
		'userId'
	);

	protected string $primaryKey	= 'galleryAuthorId';

	protected array $indices		= array(
		'galleryId',
		'userId'
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
