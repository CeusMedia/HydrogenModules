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
class Model_Bookmark_Tag extends Model
{
	protected string $name			= 'bookmark_tags';

	protected array $columns		= [
		'bookmarkTagId',
		'bookmarkId',
		'userId',
		'title',
		'createdAt',
		'relatedAt',
		'usedAt',
	];

	protected string $primaryKey	= 'bookmarkTagId';

	protected array $indices		= [
		'bookmarkId',
		'userId',
		'createdAt',
		'relatedAt',
		'usedAt',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
