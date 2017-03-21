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
class Model_Catalog_Bookstore_Article_Category extends CMF_Hydrogen_Model {

	protected $name		= 'catalog_bookstore_article_categories';
	protected $columns	= array(
		"articleCategoryId",
		"articleId",
		"categoryId",
		"volume",
	);
	protected $primaryKey	= 'articleCategoryId';
	protected $indices		= array(
		"articleId",
		"categoryId"
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
