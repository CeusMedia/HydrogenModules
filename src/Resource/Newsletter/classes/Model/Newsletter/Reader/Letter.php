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
class Model_Newsletter_Reader_Letter extends Model
{
	const STATUS_REMOVED	= -3;
	const STATUS_CANCELLED	= -2;
	const STATUS_FAILED		= -1;
	const STATUS_ENQUEUED	= 0;
	const STATUS_SENT		= 1;
	const STATUS_OPENED		= 2;

	const STATUSES			= array(
		self::STATUS_REMOVED,
		self::STATUS_CANCELLED,
		self::STATUS_FAILED,
		self::STATUS_ENQUEUED,
		self::STATUS_SENT,
		self::STATUS_OPENED,
	);

	protected string $name		= 'newsletter_reader_letters';

	protected array $columns	= array(
		'newsletterReaderLetterId',
		'newsletterReaderId',
		'newsletterQueueId',
		'newsletterId',
		'mailId',
		'status',
		'enqueuedAt',
		'sentAt',
		'openedAt',
	);

	protected string $primaryKey	= 'newsletterReaderLetterId';

	protected array $indices		= array(
		'newsletterReaderId',
		'newsletterQueueId',
		'newsletterId',
		'mailId',
		'status',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
