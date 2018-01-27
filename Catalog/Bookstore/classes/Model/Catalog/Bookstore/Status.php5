<?php
/**
 *	Data model of bookstore states.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
/**
 *	Data model of bookstore states.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
class Model_Catalog_Bookstore_Status extends CMF_Hydrogen_Model {

	protected $name		= 'catalog_bookstore_states';
	protected $columns	= array(
		'statusId',
		'title',
		'available',
		'rank',
	);
	protected $primaryKey	= 'statusId';
	protected $indices		= array(
		"available",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>