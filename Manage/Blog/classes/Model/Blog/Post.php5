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
class Model_Blog_Post extends CMF_Hydrogen_Model {

	protected $name		= 'blog_posts';
	protected $columns	= array(
		'postId',
		'authorId',
		'status',
		'language',
		'title',
		'abstract',
		'content',
		'nrViews',
		'createdAt',
		'modifiedAt',
		'viewedAt',
	);
	protected $primaryKey	= 'postId';
	protected $indices		= array(
		'authorId',
		'status',
		'language',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
