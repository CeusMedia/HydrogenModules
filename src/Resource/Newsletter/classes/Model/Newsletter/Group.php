<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2020 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2020 Ceus Media
 */
class Model_Newsletter_Group extends Model
{
	public const STATUS_DISCARDED	= -1;
	public const STATUS_NEW			= 0;
	public const STATUS_USABLE		= 1;

	public const STATUSES			= [
		self::STATUS_DISCARDED,
		self::STATUS_NEW,
		self::STATUS_USABLE,
	];

	public const TYPE_DEFAULT		= 0;
	public const TYPE_TEST			= 1;
	public const TYPE_AUTOMATIC		= 2;
	public const TYPE_HIDDEN		= 3;

	public const TYPES				= [
		self::TYPE_DEFAULT,
		self::TYPE_TEST,
		self::TYPE_AUTOMATIC,
		self::TYPE_HIDDEN,
	];

	protected string $name			= 'newsletter_groups';

	protected array $columns		= [
		'newsletterGroupId',
		'creatorId',
		'status',
		'type',
		'title',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'newsletterGroupId';

	protected array $indices		= [
		'creatorId',
		'status',
		'type',
		'title',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
