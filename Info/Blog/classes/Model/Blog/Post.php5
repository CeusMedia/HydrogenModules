<?php
/**
 *	Blog Post Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
/**
 *	Blog Post Model.
 *	@category		...
 *	@package		...
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Blog_Post extends CMF_Hydrogen_Model
{
	protected $name		= 'blog_posts';

	protected $columns	= array(
		'postId',
		'parentId',
		'authorId',
		'categoryId',
		'status',
		'language',
		'title',
		'abstract',
		'content',
		'nrViews',
		'nrLikes',
		'nrDislikes',
		'createdAt',
		'modifiedAt',
		'viewedAt',
		'commentedAt',
	);

	protected $primaryKey	= 'postId';

	protected $indices		= array(
		'parentId',
		'authorId',
		'categoryId',
		'status',
		'language',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
