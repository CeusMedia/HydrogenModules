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
class Model_Catalog_Article_Category extends CMF_Hydrogen_Model {

	protected $name		= 'article_categories';
	protected $columns	= array(
		"article_category_id",
		"article_id",
		"category_id",
		"volume",
	);
	protected $primaryKey	= 'article_category_id';
	protected $indices		= array(
		"article_id",
		"category_id"
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
