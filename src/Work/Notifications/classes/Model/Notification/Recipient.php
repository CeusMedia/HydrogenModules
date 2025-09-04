<?php

use CeusMedia\HydrogenFramework\Model\Database\Table;

class Model_Notification_Recipient extends Table
{
	public const PRIORITY_LOW			= 0;
	public const PRIORITY_NORMAL		= 1;
	public const PRIORITY_HIGH			= 2;

	public const PRIORITIES				= [
		self::PRIORITY_HIGH,
		self::PRIORITY_NORMAL,
		self::PRIORITY_LOW,
	];

	public const STATUS_ABORTED			= -1;
	public const STATUS_NEW				= 0;
	public const STATUS_ACTIVE			= 1;
	public const STATUS_SEEN			= 2;

	public const STATUSES				= [
		self::STATUS_ABORTED,
		self::STATUS_NEW,
		self::STATUS_ACTIVE,
		self::STATUS_SEEN,
	];

	public const TYPE_NONE				= 0;
	public const TYPE_INFO				= 1;
	public const TYPE_WARNING			= 2;
	public const TYPE_SUCCESS			= 3;

	public const TYPES				= [
		self::TYPE_NONE,
		self::TYPE_INFO,
		self::TYPE_WARNING,
		self::TYPE_SUCCESS,
	];

	/**	@var	$name		string		Table name without prefix of database connection */
	protected string $name				= 'notification_recipients';

	/**	@var	array		$columns	List of columns within table */
	protected array $columns			= [
		'notificationRecipientId',
		'notificationMessageId',
		'userId',
		'status',
		'createdAt',
		'modifiedAt',
	];

	/**	@var	string		$primaryKey		Name of column with primary key */
	protected string $primaryKey			= 'notificationRecipientId';

	/**	@var	array		$name			List of columns which are a foreign key and/or indexed */
	protected array $indices				= [
		'notificationMessageId',
		'userId',
		'status',
	];

	/**	@var	integer		$fetchMode		Fetch mode, see PDO documentation */
	protected int $fetchMode				= PDO::FETCH_CLASS;

	/** @var	?string		$className		Entity class to use */
	protected ?string $className			= 'Entity_Notification_Recipient';
}