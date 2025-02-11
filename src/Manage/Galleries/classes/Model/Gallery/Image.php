<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Gallery_Image extends Model
{
	protected string $name			= 'gallery_images';

	protected array $columns		= [
		'galleryImageId',
		'galleryId',
		'rank',
		'filename',
		'title',
		'description',
		'timestamp',
	];

	protected string $primaryKey	= 'galleryImageId';

	protected array $indices		= [
		'galleryId',
		'rank',
		'filename',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
