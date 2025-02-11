<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Bookmark_Comment extends Model
{
	protected string $name			= 'bookmark_comments';

	protected array $columns		= [
		'bookmarkCommentId',
		'bookmarkId',
		'userId',
		'status',
		'votes',
		'content',
		'createdAt',
		'modifiedAt',
		'votedAt',
	];

	protected string $primaryKey	= 'bookmarkCommentId';

	protected array $indices		= [
		'bookmarkId',
		'userId',
		'status',
		'votes',
		'createdAt',
		'modifiedAt',
		'votedAt',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
