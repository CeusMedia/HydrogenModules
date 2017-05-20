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
class Model_Blog_Comment extends CMF_Hydrogen_Model {

	protected $name		= 'blog_comments';
	protected $columns	= array(
		'commentId',
		'parentId',
		'postId',
		'authorId',
		'status',
		'language',
		'username',
		'email',
		'title',
		'content',
		'nrLikes',
		'nrDislikes',
		'createdAt',
		'modifiedAt',
		'repliedAt',
	);
	protected $primaryKey	= 'commentId';
	protected $indices		= array(
		'parentId',
		'postId',
		'authorId',
		'status',
		'language',
		'username',
		'email',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
