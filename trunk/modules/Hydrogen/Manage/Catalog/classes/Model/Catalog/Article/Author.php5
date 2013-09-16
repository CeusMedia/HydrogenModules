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
 *	@uses			Author
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			20.1.2005
 *	@version		3.0
 */
class Model_Catalog_Article_Author extends CMF_Hydrogen_Model {

	protected $name		= 'article_authors';
	protected $columns	= array(
		"article_author_id",
		"article_id",
		"author_id",
		"editor",
	);
	protected $primaryKey	= 'article_author_id';
	protected $indices		= array(
		"article_id",
		"author_id",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
