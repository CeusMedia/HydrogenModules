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
class Model_Catalog_Article_Document extends CMF_Hydrogen_Model {

	protected $name		= 'article_documents';
	protected $columns	= array(
		'article_document_id',
		'article_id',
		'status',
		'type',
		'url',
		'title',
	);
	protected $primaryKey	= 'article_document_id';
	protected $indices		= array(
		"article_id",
		"status",
		"type",
		"url",
		'title',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
