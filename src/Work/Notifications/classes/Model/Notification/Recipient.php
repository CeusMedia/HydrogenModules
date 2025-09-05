<?php

use CeusMedia\HydrogenFramework\Model\Database\Table;

class Model_Notification_Recipient extends Table
{
	public const STATUS_NEW				= 0;
	public const STATUS_SEEN			= 1;

	public const STATUSES				= [
		self::STATUS_NEW,
		self::STATUS_SEEN,
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
