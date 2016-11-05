<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 *	@version		$Id$
 */
/**
 *	...
 *	@category		...
 *	@package		...
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 *	@version		$Id$
 */
class Model_Bookmark_Comment extends CMF_Hydrogen_Model {

	protected $name		= 'bookmark_comments';
	protected $columns	= array(
		'bookmarkCommentId',
		'bookmarkId',
		'userId',
		'status',
		'votes',
		'content',
		'createdAt',
		'modifiedAt',
		'votedAt',
	);
	protected $primaryKey	= 'bookmarkCommentId';
	protected $indices		= array(
		'bookmarkId',
		'userId',
		'status',
		'votes',
		'createdAt',
		'modifiedAt',
		'votedAt',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
