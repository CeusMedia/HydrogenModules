<?php
/**
 *	Data Model of Author.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Author.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@uses			Author
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Catalog_Article_Author extends Model
{
	protected $name		= 'catalog_article_authors';

	protected $columns	= array(
		"articleAuthorId",
		"articleId",
		"authorId",
		"editor",
	);

	protected $primaryKey	= 'articleAuthorId';

	protected $indices		= array(
		"articleId",
		"authorId",
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
