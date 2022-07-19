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
 *	@uses			ArticleAuthor
 *	@uses			TimeConverter
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Catalog_Article extends Model
{
	protected $name		= 'catalog_articles';

	protected $columns	= array(
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

	protected $primaryKey	= 'articleId';

	protected $indices		= array(
		"status",
		"title",
		"isn",
		"series",
		"price",
		"new"
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
