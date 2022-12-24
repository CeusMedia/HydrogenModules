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
	protected string $name			= 'catalog_categories';

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'categoryId';

	protected array $indices		= [
		"parentId",
		"visible",
		"issn",
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
