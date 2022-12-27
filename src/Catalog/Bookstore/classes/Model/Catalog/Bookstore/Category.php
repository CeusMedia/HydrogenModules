<?php
/**
 *	Data Model of Bookstore Categories.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Bookstore Categories.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Catalog_Bookstore_Category extends Model
{
	protected string $name			= 'catalog_bookstore_categories';

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
