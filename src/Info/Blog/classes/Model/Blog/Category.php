<?php
/**
 *	Blog Post Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Blog Post Model.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Blog_Category extends Model
{
	protected string $name		= 'blog_categories';

	protected array $columns	= array(
		'categoryId',
		'parentId',
		'status',
		'language',
		'title',
		'content',
		'nrPosts',
		'nrComments',
		'createdAt',
		'modifiedAt',
		'postedAt',
		'commentedAt',
	);

	protected string $primaryKey	= 'categoryId';

	protected array $indices		= array(
		'parentId',
		'status',
		'language',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
