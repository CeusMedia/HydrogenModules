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
class Model_Newsletter_Reader_Group extends Model
{
	public const STATUS_REMOVED		= -2;
	public const STATUS_CANCELLED	= -1;
	public const STATUS_ASSIGNED	= 0;
	public const STATUS_REJOINED	= 1;

	public const STATUSES			= [
		self::STATUS_REMOVED,
		self::STATUS_CANCELLED,
		self::STATUS_ASSIGNED,
		self::STATUS_REJOINED,
	];

	protected string $name			= 'newsletter_reader_groups';

	protected array $columns		= [
		'newsletterReaderGroupId',
		'newsletterReaderId',
		'newsletterGroupId',
		'status',
		'createdAt',
	];

	protected string $primaryKey	= 'newsletterReaderGroupId';

	protected array $indices		= [
		'newsletterReaderId',
		'newsletterGroupId',
		'status',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
