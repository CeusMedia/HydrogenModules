<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */
/**
 *	...
 *	@category		...
 *	@package		...
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */
class Model_Gallery_Image extends CMF_Hydrogen_Model
{
	protected $name		= 'gallery_images';

	protected $columns	= array(
		'galleryImageId',
		'galleryId',
		'rank',
		'filename',
		'title',
		'description',
		'timestamp',
	);

	protected $primaryKey	= 'galleryImageId';

	protected $indices		= array(
		'galleryId',
		'rank',
		'filename',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
