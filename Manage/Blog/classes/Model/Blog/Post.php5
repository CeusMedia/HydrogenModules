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
class Model_Blog_Post extends Model
{
	protected $name		= 'blog_posts';

	protected $columns	= array(
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
	);

	protected $primaryKey	= 'postId';

	protected $indices		= array(
		'parentId',
		'authorId',
		'categoryId',
		'status',
		'language',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
