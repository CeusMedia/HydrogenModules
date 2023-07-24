<?php
/**
 *	Data Model of Articles.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Articles.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Catalog_Bookstore_Article extends Model
{
	protected string $name			= 'catalog_bookstore_articles';

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'articleId';

	protected array $indices		= [
		"status",
		"title",
		"isn",
		"series",
		"price",
		"new"
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
