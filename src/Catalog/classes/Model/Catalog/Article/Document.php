<?php
/**
 *	Data Model of Category.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Branch.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Catalog_Article_Document extends Model
{
	protected string $name			= 'catalog_article_documents';

	protected array $columns		= [
		'articleDocumentId',
		'articleId',
		'status',
		'type',
		'url',
		'title',
	];

	protected string $primaryKey	= 'articleDocumentId';

	protected array $indices		= [
		"articleId",
		"status",
		"type",
		"url",
		'title',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
