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
class Model_News extends Model
{
	public const STATUS_HIDDEN		= -1;
	public const STATUS_NEW			= 0;
	public const STATUS_PUBLIC		= 1;

	public const STATUSES			= [
		self::STATUS_HIDDEN,
		self::STATUS_NEW,
		self::STATUS_PUBLIC,
	];

	protected string $name			= 'news';

	protected array $columns		= [
		'newsId',
		'status',
		'type',
		'title',
		'content',
		'columns',
		'startsAt',
		'endsAt',
		'createdAt',
	];

	protected string $primaryKey	= 'newsId';

	protected array $indices		= [
		'status',
		'type',
		'title',
		'startsAt',
		'endsAt',
		'createdAt',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
