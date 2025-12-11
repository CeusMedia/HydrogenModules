<?php
/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2025 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2025 Ceus Media (https://ceusmedia.de/)
 */
class Model_News extends Model
{
	public const STATUS_HIDDEN		= -1;
	public const STATUS_NEW			= 0;
	public const STATUS_PUBLIC		= 1;
	public const STATUS_OUTDATED	= 2;

	public const STATUSES			= [
		self::STATUS_HIDDEN,
		self::STATUS_NEW,
		self::STATUS_PUBLIC,
		self::STATUS_OUTDATED,
	];

	protected string $name			= 'news';

	protected array $columns		= [
		'newsId',
		'status',
		'title',
		'content',
		'columns',
		'startsAt',
		'endsAt',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'newsId';

	protected array $indices		= [
		'status',
		'title',
		'columns',
		'startsAt',
		'endsAt',
		'createdAt',
		'modifiedAt',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
