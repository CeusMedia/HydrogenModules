<?php
/**
 *	Forum Thread Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 */
/**
 *	Forum Thread Model.
 *	@category		...
 *	@package		...
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 */
class Model_Forum_Thread extends CMF_Hydrogen_Model
{
	protected $name		= 'forum_threads';

	protected $columns	= array(
		'threadId',
		'topicId',
		'authorId',
		'type',
		'status',
		'title',
		'createdAt',
		'modifiedAt'
	);

	protected $primaryKey	= 'threadId';

	protected $indices		= array(
		'topicId',
		'authorId',
		'type',
		'status',
		'title',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
