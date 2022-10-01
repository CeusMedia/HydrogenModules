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
	protected string $name		= 'bookmarks';

	protected array $columns	= array(
		'bookmarkId',
		'status',
		'url',
		'title',
		'createdAt',
		'checkedAt',
		'usedAt',
	);

	protected string $primaryKey	= 'bookmarkId';

	protected array $indices		= array(
		'status',
		'url',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
