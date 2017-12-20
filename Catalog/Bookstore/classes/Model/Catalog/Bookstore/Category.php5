<?php
/**
 *	Data Model of Bookstore Categories.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
/**
 *	Data Model of Bookstore Categories.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
class Model_Catalog_Bookstore_Category extends CMF_Hydrogen_Model {

	protected $name		= 'catalog_bookstore_categories';
	protected $columns	= array(
		'categoryId',
		'parentId',
		'visible',
		'rank',
		'issn',
		'publisher',
		'label_de',
		'label_en',
		'label_it',
		'label_former'
	);
	protected $primaryKey	= 'categoryId';
	protected $indices		= array(
		"parentId",
		"visible",
		"issn",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
