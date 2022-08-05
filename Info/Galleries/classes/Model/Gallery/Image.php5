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
class Model_Gallery_Image extends Model
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
