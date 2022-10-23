<?php
/**
 *	Data Model of Bookstore Articles.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of BookstoreArticles.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Catalog_Bookstore_Article extends Model
{
	protected string $name		= 'catalog_bookstore_articles';

	protected array $columns	= array(
		"articleId",
		"status",
		"title",
		"subtitle",
		"description",
		"recension",
		"publication",
		"size",
		"digestion",
		"weight",
		"isn",
		"series",
		"price",
		"cover",
		"language",
		"new",
		"createdAt",
		"modifiedAt",
	);

	protected string $primaryKey	= 'articleId';

	protected array $indices		= array(
		"status",
		"title",
		"isn",
		"series",
		"price",
		"new"
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
