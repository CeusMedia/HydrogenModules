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
class Model_Blog_Category extends Model
{
	protected $name		= 'blog_categories';

	protected $columns	= array(
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

	protected $primaryKey	= 'categoryId';

	protected $indices		= array(
		'parentId',
		'status',
		'language',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
