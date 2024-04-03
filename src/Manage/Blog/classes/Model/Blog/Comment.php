<?php
/**
 *	Blog Post Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Blog Post Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Blog_Comment extends Model
{
	protected string $name		= 'blog_comments';

	protected array $columns	= [
		'commentId',
		'parentId',
		'postId',
		'authorId',
		'status',
		'language',
		'username',
		'email',
		'content',
		'nrLikes',
		'nrDislikes',
		'createdAt',
		'repliedAt',
	];

	protected string $primaryKey	= 'commentId';

	protected array $indices		= [
		'parentId',
		'postId',
		'authorId',
		'status',
		'language',
		'username',
		'email',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
