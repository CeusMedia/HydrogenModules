<?php
/**
 *	Forum Thread Topic Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
/**
 *	Forum Thread Topic Model.
 *	@category		...
 *	@package		...
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
class Model_Forum_Topic extends CMF_Hydrogen_Model {

	protected $name		= 'forum_topics';
	protected $columns	= array(
		'topicId',
		'parentId',
		'authorId',
		'type',
		'status',
		'title',
		'description',
		'createdAt',
		'modifiedAt'
	);
	protected $primaryKey	= 'topicId';
	protected $indices		= array(
		'parentId',
		'authorId',
		'type',
		'status',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
