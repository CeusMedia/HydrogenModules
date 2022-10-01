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
class Model_Catalog_Gallery_Category extends Model
{
	protected string $name		= 'catalog_gallery_categories';

	protected array $columns	= array(
		'galleryCategoryId',
		'parentId',
		'status',
		'rank',
		'path',
		'title',
		'price',
		'image',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'galleryCategoryId';

	protected array $indices		= array(
		'parentId',
		'status',
		'path',
		'rank',
		'createdAt',
		'modifiedAt',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
