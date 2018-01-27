<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 *	@version		$Id$
 */
/**
 *	...
 *	@category		...
 *	@package		...
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 *	@version		$Id$
 */
class Model_Catalog_Gallery_Image extends CMF_Hydrogen_Model {

	protected $name		= 'catalog_gallery_images';
	protected $columns	= array(
		'galleryImageId',
		'galleryCategoryId',
		'status',
		'type',
		'filename',
		'rank',
		'title',
		'price',
		'takenAt',
		'createdAt',
		'modifiedAt',
	);
	protected $primaryKey	= 'galleryImageId';
	protected $indices		= array(
		'galleryCategoryId',
		'status',
		'type',
		'filename',
		'rank',
		'createdAt',
		'modifiedAt',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
