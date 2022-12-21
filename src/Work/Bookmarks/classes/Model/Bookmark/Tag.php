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
	protected string $name		= 'bookmark_tags';

	protected array $columns	= array(
		'bookmarkTagId',
		'bookmarkId',
		'userId',
		'title',
		'createdAt',
		'relatedAt',
		'usedAt',
	);

	protected string $primaryKey	= 'bookmarkTagId';

	protected array $indices		= array(
		'bookmarkId',
		'userId',
		'createdAt',
		'relatedAt',
		'usedAt',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
