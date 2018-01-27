<?php
/**
 *	Data model of articles.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
/**
 *	Data model of articles.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
class Model_Catalog_Clothing_Article extends CMF_Hydrogen_Model {

	protected $name		= 'catalog_clothing_articles';
	protected $columns	= array(
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
	);
	protected $primaryKey	= 'articleId';
	protected $indices		= array(
		"categoryId",
		"status",
		"gender",
		"form",
		"size",
		"color",
		"part",
		"quantity",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
