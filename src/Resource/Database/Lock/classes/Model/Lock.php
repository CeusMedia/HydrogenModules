<?php
/**
 *	Model.
 *	@version		$Id$
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Model.
 *	@version		$Id$
 *	@todo			implement
 *	@todo			code documentation
 */
class Model_Lock extends Model
{
	/**	@var	string		$name		Table name without prefix of database connection */
	protected string $name				= "locks";

	/**	@var	array		$columns	List of columns within table */
	protected array $columns			= [
		'lockId',
		'userId',
		'subject',
		'entryId',
		'timestamp',
	];

	/**	@var	string		$primaryKey	Name of column with primary key */
	protected string $primaryKey		= "lockId";

	/**	@var	array		$indices	List of columns which are a foreign key and/or indexed */
	protected array $indices			= [
		'userId',
		'subject',
		'entryId',
	];

	/**	@var	integer		$fetchMode	Fetch mode, see PDO documentation */
	protected int $fetchMode			= PDO::FETCH_OBJ;
}
