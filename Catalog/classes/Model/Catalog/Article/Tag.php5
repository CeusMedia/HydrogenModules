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
	protected $name		= 'catalog_article_tags';

	protected $columns	= array(
		"articleTagId",
		"articleId",
		"tag",
	);

	protected $primaryKey	= 'articleTagId';

	protected $indices		= array(
		"articleId",
		"tag"
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
