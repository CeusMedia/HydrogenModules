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
class Model_Catalog_Bookstore_Article_Tag extends Model
{
	protected string $name		= 'catalog_bookstore_article_tags';

	protected array $columns	= array(
		"articleTagId",
		"articleId",
		"tag",
	);

	protected string $primaryKey	= 'articleTagId';

	protected array $indices		= array(
		"articleId",
		"tag"
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}