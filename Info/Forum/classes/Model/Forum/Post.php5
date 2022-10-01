<?php
/**
 *	Forum Thread Post Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Forum Thread Post Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 */
class Model_Forum_Post extends Model
{
	protected string $name		= 'forum_posts';

	protected array $columns	= array(
		'postId',
		'threadId',
		'authorId',
		'type',
		'status',
		'content',
		'createdAt',
		'modifiedAt'
	);

	protected string $primaryKey	= 'postId';

	protected array $indices		= array(
		'threadId',
		'authorId',
		'type',
		'status',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
