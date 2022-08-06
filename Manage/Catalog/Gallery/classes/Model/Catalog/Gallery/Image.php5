<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */
class Model_Catalog_Gallery_Image extends Model
{
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
