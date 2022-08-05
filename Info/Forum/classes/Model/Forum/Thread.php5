<?php
/**
 *	Forum Thread Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Forum Thread Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 */
class Model_Forum_Thread extends Model
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
