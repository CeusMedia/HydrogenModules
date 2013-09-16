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
class Model_Catalog_Author extends CMF_Hydrogen_Model {

	protected $name		= 'authors';
	protected $columns	= array(
		"author_id",
		"lastname",
		"firstname",
//		"institution",
		"image",
		"reference",
		"description",
	);
	protected $primaryKey	= 'author_id';
	protected $indices		= array(
		"lastname",
		"image",
		"reference",
//		"institution",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
