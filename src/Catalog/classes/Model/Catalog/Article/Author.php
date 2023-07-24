<?php
/**
 *	Data Model of Author.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Author.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Catalog_Article_Author extends Model
{
	protected string $name			= 'catalog_article_authors';

	protected array $columns		= [
		"articleAuthorId",
		"articleId",
		"authorId",
		"editor",
	];

	protected string $primaryKey	= 'articleAuthorId';

	protected array $indices		= [
		"articleId",
		"authorId",
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
