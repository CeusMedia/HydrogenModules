<?php
/**
 *	Blog Post Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Blog Post Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 */
class Model_Blog_Comment extends Model
{
	protected string $name		= 'blog_comments';

	protected array $columns	= array(
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
	);

	protected $primaryKey	= 'commentId';

	protected array $indices		= array(
		'parentId',
		'postId',
		'authorId',
		'status',
		'language',
		'username',
		'email',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
