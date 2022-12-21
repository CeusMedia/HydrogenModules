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
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Catalog_Bookstore_Article_Author extends Model
{
	protected string $name		= 'catalog_bookstore_article_authors';

	protected array $columns	= array(
		"articleAuthorId",
		"articleId",
		"authorId",
		"editor",
	);

	protected string $primaryKey	= 'articleAuthorId';

	protected array $indices		= array(
		"articleId",
		"authorId",
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
