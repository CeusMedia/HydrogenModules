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
class Model_Catalog_Bookstore_Article_Category extends Model
{
	protected $name		= 'catalog_bookstore_article_categories';

	protected $columns	= array(
		"articleCategoryId",
		"articleId",
		"categoryId",
		"rank",
		"volume",
	);

	protected $primaryKey	= 'articleCategoryId';

	protected $indices		= array(
		"articleId",
		"categoryId"
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
