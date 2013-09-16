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
class Model_Catalog_Article_Tag extends CMF_Hydrogen_Model {

	protected $name		= 'article_tags';
	protected $columns	= array(
		"articleTagId",
		"articleId",
		"tag",
	);
	protected $primaryKey	= 'articleTagId';
	protected $indices		= array(
		"articleId",
		"tag"
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
