<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013 Ceus Media
 */
class Model_Bookmark_Comment extends Model
{
	protected string $name		= 'bookmark_comments';

	protected array $columns	= array(
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

	protected string $primaryKey	= 'bookmarkCommentId';

	protected array $indices		= array(
		'bookmarkId',
		'userId',
		'status',
		'votes',
		'createdAt',
		'modifiedAt',
		'votedAt',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
