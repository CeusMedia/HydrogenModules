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
class Model_Catalog_Bookstore_Article_Author extends CMF_Hydrogen_Model {

	protected $name		= 'catalog_bookstore_article_authors';
	protected $columns	= array(
		"articleAuthorId",
		"articleId",
		"authorId",
		"editor",
	);
	protected $primaryKey	= 'articleAuthorId';
	protected $indices		= array(
		"articleId",
		"authorId",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
