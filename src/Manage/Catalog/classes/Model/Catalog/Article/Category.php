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
class Model_Catalog_Article_Category extends Model
{
	protected string $name			= 'catalog_article_categories';

	protected array $columns		= [
		"articleCategoryId",
		"articleId",
		"categoryId",
		"volume",
	];

	protected string $primaryKey	= 'articleCategoryId';

	protected array $indices		= [
		"articleId",
		"categoryId"
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
