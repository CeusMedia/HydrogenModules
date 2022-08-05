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
class Model_Bookmark extends Model
{
	protected $name		= 'bookmarks';

	protected $columns	= array(
		'bookmarkId',
		'status',
		'url',
		'title',
		'createdAt',
		'checkedAt',
		'usedAt',
	);

	protected $primaryKey	= 'bookmarkId';

	protected $indices		= array(
		'status',
		'url',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
