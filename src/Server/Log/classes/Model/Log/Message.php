<?php

use CeusMedia\HydrogenFramework\Model;

class Model_Log_Message extends Model
{

	public const TYPE_ERROR			= 1;
	public const TYPE_WARNING		= 2;
	public const TYPE_NOTICE		= 4;
	public const TYPE_INFO			= 8;
	public const TYPE_DEBUG			= 16;

	public const STATUS_NEW			= 0;
	public const STATUS_SEEN		= 1;
	public const STATUS_MANAGED		= 2;
	public const STATUS_CLOSED		= 4;

	public const FORMAT_TEXT		= 0;
	public const FORMAT_PHP			= 1;
	public const FORMAT_JSON		= 2;
	public const FORMAT_XML			= 4;
	public const FORMAT_WDDX		= 8;

	/**	@var		string				$name				Name of Database Table without Prefix */
	protected string $name				= 'log_messages';

	/**	@var		array				$columns			List of Database Table Columns */
	protected array $columns			= [
		'logMessageId',
		'type',
		'status',
		'ip',
		'format',
		'message',
		'userAgent',
		'context',
		'microtimestamp',
	];

	/**	@var		string				$primaryKey			Primary Key of Database Table */
	protected string $primaryKey		= 'logMessageId';

	/**	@var		array				$name				List of foreign Keys of Database Table */
 	protected array $indices			= [
		'type',
		'status',
		'ip',
		'format',
	];

	/**	@var		integer				$fetchMode			PDO fetch mode */
	protected int $fetchMode			= PDO::FETCH_OBJ;
}
