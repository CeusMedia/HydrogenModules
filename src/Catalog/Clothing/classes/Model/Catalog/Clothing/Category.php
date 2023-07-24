<?php
/**
 *	Data model of products.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data model of products.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Catalog_Clothing_Category extends Model
{
	protected string $name			= 'catalog_clothing_categories';

	protected array $columns		= [
		"categoryId",
		"status",
		"title",
		"description",
		"createdAt",
		"modifiedAt",
	];

	protected string $primaryKey	= 'categoryId';

	protected array $indices		= [
		"status",
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
