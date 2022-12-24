<?php
/**
 *	Data model of articles.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data model of articles.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Catalog_Clothing_Article extends Model
{
	protected string $name			= 'catalog_clothing_articles';

	protected array $columns		= [
		"articleId",
		"categoryId",
		"status",
		"gender",
		"form",
		"size",
		"color",
		"part",
		"price",
		"currency",
		"quantity",
		"title",
		"description",
		"image",
		"createdAt",
		"modifiedAt",
	];

	protected string $primaryKey	= 'articleId';

	protected array $indices		= [
		"categoryId",
		"status",
		"gender",
		"form",
		"size",
		"color",
		"part",
		"quantity",
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
