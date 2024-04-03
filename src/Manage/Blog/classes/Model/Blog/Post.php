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
class Model_Blog_Post extends Model
{
	protected string $name		= 'blog_posts';

	protected array $columns	= [
		'postId',
		'parentId',
		'authorId',
		'categoryId',
		'status',
		'language',
		'title',
		'abstract',
		'content',
		'nrViews',
		'nrLikes',
		'nrDislikes',
		'createdAt',
		'modifiedAt',
		'viewedAt',
		'commentedAt',
	];

	protected string $primaryKey	= 'postId';

	protected array $indices		= [
		'parentId',
		'authorId',
		'categoryId',
		'status',
		'language',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
