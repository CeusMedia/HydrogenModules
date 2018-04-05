<?php
class Model_Log_Message extends CMF_Hydrogen_Model{

	const TYPE_ERROR		= 1;
	const TYPE_WARNING		= 2;
	const TYPE_NOTICE		= 4;
	const TYPE_INFO			= 8;
	const TYPE_DEBUG		= 16;

	const STATUS_NEW		= 0;
	const STATUS_SEEN		= 1;
	const STATUS_MANAGED	= 2;
	const STATUS_CLOSED		= 4;

	const FORMAT_TEXT		= 0;
	const FORMAT_PHP		= 1;
	const FORMAT_JSON		= 2;
	const FORMAT_XML		= 4;
	const FORMAT_WDDX		= 8;

	/**	@var		string				$name				Name of Database Table without Prefix */
	protected $name						= 'log_messages';

	/**	@var		array				$columns			List of Database Table Columns */
	protected $columns	= array(
		'logMessageId',
		'type',
		'status',
		'ip',
		'format',
		'message',
		'userAgent',
		'context',
		'microtimestamp',
	);

	/**	@var		string				$primaryKey			Primary Key of Database Table */
	protected $primaryKey				= 'logMessageId';

	/**	@var		array				$name				List of foreign Keys of Database Table */
 	protected $indices					= array(
		'type',
		'status',
		'ip',
		'format',
	);

	/**	@var		integer				$fetchMode			PDO fetch mode */
	protected $fetchMode				= PDO::FETCH_OBJ;
}
