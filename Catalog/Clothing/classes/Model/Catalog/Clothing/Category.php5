<?php
/**
 *	Data model of products.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
/**
 *	Data model of products.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
class Model_Catalog_Clothing_Category extends CMF_Hydrogen_Model {

	protected $name		= 'catalog_clothing_categories';
	protected $columns	= array(
		"categoryId",
		"status",
		"title",
		"description",
		"createdAt",
		"modifiedAt",
	);
	protected $primaryKey	= 'categoryId';
	protected $indices		= array(
		"status",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
