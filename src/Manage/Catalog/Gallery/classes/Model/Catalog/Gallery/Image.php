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
class Model_Catalog_Gallery_Image extends Model
{
	protected string $name			= 'catalog_gallery_images';

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'galleryImageId';

	protected array $indices		= [
		'galleryCategoryId',
		'status',
		'type',
		'filename',
		'rank',
		'createdAt',
		'modifiedAt',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
