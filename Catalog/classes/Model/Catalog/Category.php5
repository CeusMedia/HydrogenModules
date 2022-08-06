<?php
/**
 *	Data Model of Category.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Branch.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Catalog_Category extends Model
{
	protected $name		= 'catalog_categories';

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
