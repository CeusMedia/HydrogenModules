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
	public const STATUS_REMOVED		= -3;
	public const STATUS_CANCELLED	= -2;
	public const STATUS_FAILED		= -1;
	public const STATUS_ENQUEUED	= 0;
	public const STATUS_SENT		= 1;
	public const STATUS_OPENED		= 2;

	public const STATUSES			= [
		self::STATUS_REMOVED,
		self::STATUS_CANCELLED,
		self::STATUS_FAILED,
		self::STATUS_ENQUEUED,
		self::STATUS_SENT,
		self::STATUS_OPENED,
	];

	protected string $name			= 'newsletter_reader_letters';

	protected array $columns		= [
		'newsletterReaderLetterId',
		'newsletterReaderId',
		'newsletterQueueId',
		'newsletterId',
		'mailId',
		'status',
		'enqueuedAt',
		'sentAt',
		'openedAt',
	];

	protected string $primaryKey	= 'newsletterReaderLetterId';

	protected array $indices		= [
		'newsletterReaderId',
		'newsletterQueueId',
		'newsletterId',
		'mailId',
		'status',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
