<?php
/**
 *	Data Model of Bookstore Categories.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Bookstore Categories.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Catalog_Bookstore_Category extends Model
{
	protected $name		= 'catalog_bookstore_categories';

	protected $columns	= array(
		'categoryId',
		'parentId',
		'visible',
		'rank',
		'issn',
		'publisher',
		'label_de',
		'label_en',
		'label_it',
		'label_former'
	);

	protected $primaryKey	= 'categoryId';

	protected $indices		= array(
		"parentId",
		"visible",
		"issn",
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
