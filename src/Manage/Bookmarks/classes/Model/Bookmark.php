<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Bookmark extends Model
{
	protected string $name			= 'bookmarks';

	protected array $columns		= [
		'bookmarkId',
		'status',
		'url',
		'title',
		'createdAt',
		'checkedAt',
		'usedAt',
	];

	protected string $primaryKey	= 'bookmarkId';

	protected array $indices		= [
		'status',
		'url',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
