<?php
/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2020 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2020 Ceus Media
 */
class Model_Newsletter_Queue extends Model
{
	public const STATUS_REJECTED	= -2;
	public const STATUS_CANCELLED	= -1;
	public const STATUS_NEW			= 0;
	public const STATUS_RUNNING		= 1;
	public const STATUS_DONE		= 2;

	public const STATUSES			= [
		self::STATUS_REJECTED,
		self::STATUS_CANCELLED,
		self::STATUS_NEW,
		self::STATUS_RUNNING,
		self::STATUS_DONE,
	];

	protected string $name			= 'newsletter_queues';

	protected array $columns		= [
		'newsletterQueueId',
		'newsletterId',
		'creatorId',
		'status',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'newsletterQueueId';

	protected array $indices		= [
		'newsletterId',
		'creatorId',
		'status',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
