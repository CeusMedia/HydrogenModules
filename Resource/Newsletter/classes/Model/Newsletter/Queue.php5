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
class Model_Newsletter_Queue extends Model
{
	const STATUS_REJECTED	= -2;
	const STATUS_CANCELLED	= -1;
	const STATUS_NEW		= 0;
	const STATUS_RUNNING	= 1;
	const STATUS_DONE		= 2;

	const STATUSES			= array(
		self::STATUS_REJECTED,
		self::STATUS_CANCELLED,
		self::STATUS_NEW,
		self::STATUS_RUNNING,
		self::STATUS_DONE,
	);

	protected string $name		= 'newsletter_queues';

	protected array $columns	= array(
		'newsletterQueueId',
		'newsletterId',
		'creatorId',
		'status',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'newsletterQueueId';

	protected array $indices		= array(
		'newsletterId',
		'creatorId',
		'status',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
