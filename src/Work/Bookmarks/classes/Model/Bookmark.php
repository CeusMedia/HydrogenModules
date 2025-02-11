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
	public const STATUS_REMOVED		= -1;
	public const STATUS_NEW			= 0;
	public const STATUS_ACTIVE		= 1;
	public const STATUS_ARCHIVED	= 2;

	public const STATUSES			= [
		self::STATUS_REMOVED,
		self::STATUS_NEW,
		self::STATUS_ACTIVE,
		self::STATUS_ARCHIVED,
	];

	protected string $name			= 'bookmarks';

	protected array $columns		= [
		'bookmarkId',
		'userId',
		'status',
		'visits',
		'url',
		'title',
		'description',
		'pageTitle',
		'pageDescription',
		'fulltext',
		'createdAt',
		'modifiedAt',
		'visitedAt',
	];

	protected string $primaryKey	= 'bookmarkId';

	protected array $indices		= [
		'userId',
		'status',
		'url',
		'createdAt',
		'modifiedAt',
		'visitedAt',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
