<?php
/**
 *	Blog Post Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
/**
 *	Blog Post Model.
 *	@category		...
 *	@package		...
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
class Model_Blog_Category extends CMF_Hydrogen_Model {

	protected $name		= 'blog_categories';
	protected $columns	= array(
		'categoryId',
		'status',
		'language',
		'title',
		'content',
		'nrViews',
		'createdAt',
		'modifiedAt',
		'viewedAt',
	);
	protected $primaryKey	= 'categoryId';
	protected $indices		= array(
		'status',
		'language',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
