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
class Model_Catalog_Article_Document extends Model
{
	protected $name		= 'catalog_article_documents';

	protected $columns	= array(
		'articleDocumentId',
		'articleId',
		'status',
		'type',
		'url',
		'title',
	);

	protected $primaryKey	= 'articleDocumentId';

	protected $indices		= array(
		"articleId",
		"status",
		"type",
		"url",
		'title',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
