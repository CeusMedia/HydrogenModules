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
class Model_Bookmark_Tag extends Model
{
	protected $name		= 'bookmark_tags';

	protected $columns	= array(
		'bookmarkTagId',
		'bookmarkId',
		'userId',
		'title',
		'createdAt',
		'relatedAt',
		'usedAt',
	);

	protected $primaryKey	= 'bookmarkTagId';

	protected $indices		= array(
		'bookmarkId',
		'userId',
		'createdAt',
		'relatedAt',
		'usedAt',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
