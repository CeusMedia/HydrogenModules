<?php
/**
 *	Forum Thread Post Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
/**
 *	Forum Thread Post Model.
 *	@category		...
 *	@package		...
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
class Model_Forum_Post extends CMF_Hydrogen_Model {

	protected $name		= 'forum_posts';
	protected $columns	= array(
		'postId',
		'threadId',
		'parentId',
		'authorId',
		'type',
		'status',
		'content',
		'createdAt',
		'modifiedAt'
	);
	protected $primaryKey	= 'postId';
	protected $indices		= array(
		'threadId',
		'parentId',
		'authorId',
		'type',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
