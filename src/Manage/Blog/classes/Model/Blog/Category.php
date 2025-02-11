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
class Model_Blog_Category extends Model
{
	protected string $name		= 'blog_categories';

	protected array $columns	= [
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
	];

	protected string $primaryKey	= 'categoryId';

	protected array $indices		= [
		'parentId',
		'status',
		'language',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
