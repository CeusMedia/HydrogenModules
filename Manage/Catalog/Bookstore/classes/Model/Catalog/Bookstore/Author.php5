<?php
/**
 *	Data Model of Author.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
/**
 *	Data Model of Author.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Neon_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
class Model_Catalog_Bookstore_Author extends CMF_Hydrogen_Model {

	protected $name		= 'catalog_bookstore_authors';
	protected $columns	= array(
		"authorId",
		"lastname",
		"firstname",
//		"institution",
		"image",
		"reference",
		"description",
	);
	protected $primaryKey	= 'authorId';
	protected $indices		= array(
		"lastname",
		"image",
		"reference",
//		"institution",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
