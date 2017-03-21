<?php
/**
 *	Data Model of Category.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
/**
 *	Data Model of Branch.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
class Model_Catalog_Bookstore_Article_Document extends CMF_Hydrogen_Model {

	protected $name		= 'catalog_bookstore_article_documents';
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
?>
