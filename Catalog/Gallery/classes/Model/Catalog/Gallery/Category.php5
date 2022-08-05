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
	protected $name		= 'catalog_gallery_categories';

	protected $columns	= array(
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

	protected $primaryKey	= 'galleryCategoryId';

	protected $indices		= array(
		'parentId',
		'status',
		'path',
		'rank',
		'createdAt',
		'modifiedAt',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
