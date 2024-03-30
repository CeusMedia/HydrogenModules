<?php
/**
 *	Data Model of Customers.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Customers.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Mail extends Model
{
	public const STATUS_ABORTED			= -3;
	public const STATUS_FAILED			= -2;
	public const STATUS_RETRY			= -1;
//	public const STATUS_PAUSED			= -1;
	public const STATUS_NEW				= 0;
	public const STATUS_SENDING			= 1;
	public const STATUS_SENT			= 2;
	public const STATUS_RECEIVED		= 3;
	public const STATUS_OPENED			= 4;
	public const STATUS_REPLIED			= 5;
	public const STATUS_ARCHIVED		= 6;

	public const COMPRESSION_UNKNOWN	= 0;
	public const COMPRESSION_NONE		= 1;
	public const COMPRESSION_BASE64		= 2;
	public const COMPRESSION_GZIP		= 3;
	public const COMPRESSION_BZIP		= 4;

	public static array $transitions	= [
		self::STATUS_ABORTED	=> [
			self::STATUS_NEW,
		],
		self::STATUS_FAILED		=> [
			self::STATUS_ABORTED,
			self::STATUS_RETRY,
			self::STATUS_NEW,
		],
		self::STATUS_RETRY		=> [
			self::STATUS_ABORTED,
			self::STATUS_FAILED,
			self::STATUS_NEW,
		],
		self::STATUS_NEW		=> [
			self::STATUS_ABORTED,
			self::STATUS_SENDING,
			self::STATUS_SENT,
		],
		self::STATUS_SENDING	=> [
			self::STATUS_FAILED,
			self::STATUS_RETRY,
			self::STATUS_SENT,
		],
		self::STATUS_SENT		=> [
			self::STATUS_RECEIVED,
			self::STATUS_OPENED,
			self::STATUS_REPLIED,
			self::STATUS_ARCHIVED,
		],
		self::STATUS_RECEIVED	=> [
			self::STATUS_OPENED,
			self::STATUS_REPLIED,
			self::STATUS_ARCHIVED,
		],
		self::STATUS_OPENED		=> [
			self::STATUS_REPLIED,
			self::STATUS_ARCHIVED,
		],
		self::STATUS_REPLIED	=> [
			self::STATUS_ARCHIVED,
		],
		self::STATUS_ARCHIVED	=> [
		],
	];

	protected string $name			= 'mails';

	protected array $columns		= [
		'mailId',
		'senderId',
		'receiverId',
		'templateId',
		'status',
		'attempts',
		'language',
		'receiverAddress',
		'receiverName',
		'senderAddress',
		'subject',
		'mailClass',
		'compression',
		'object',
		'raw',
		'enqueuedAt',
		'attemptedAt',
		'sentAt',
	];

	protected string $primaryKey	= 'mailId';

	protected array $indices		= [
		'senderId',
		'receiverId',
		'templateId',
		'status',
		'attempts',
		'language',
		'receiverAddress',
		'receiverName',
		'senderAddress',
		'subject',
		'mailClass',
		'compression',
		'enqueuedAt',
		'attemptedAt',
		'sentAt',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
