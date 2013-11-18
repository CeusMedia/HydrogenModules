<?php
/**
 *	Data Model of Articles.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
/**
 *	Data Model of Articles.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Neon_Model
 *	@uses			ArticleAuthor
 *	@uses			TimeConverter
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
class Model_Catalog_Article extends CMF_Hydrogen_Model {

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
?>
