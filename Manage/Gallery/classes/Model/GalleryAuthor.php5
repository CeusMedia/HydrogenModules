<?php
class Model_GalleryAuthor extends CMF_Hydrogen_Model{
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
?>
