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
	protected string $name		= 'forum_threads';

	protected array $columns	= array(
		'threadId',
		'topicId',
		'authorId',
		'type',
		'status',
		'title',
		'createdAt',
		'modifiedAt'
	);

	protected string $primaryKey	= 'threadId';

	protected array $indices		= array(
		'topicId',
		'authorId',
		'type',
		'status',
		'title',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
