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
class Model_Catalog_Article_Tag extends Model
{
	protected string $name			= 'catalog_article_tags';

	protected array $columns		= [
		"articleTagId",
		"articleId",
		"tag",
	];

	protected string $primaryKey	= 'articleTagId';

	protected array $indices		= [
		"articleId",
		"tag"
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
