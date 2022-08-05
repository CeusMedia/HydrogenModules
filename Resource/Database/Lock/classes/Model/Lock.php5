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
	/**	@var	$name		string		Table name without prefix of database connection */
	protected $name			= "locks";

	/**	@var	$name		string		List of columns within table */
	protected $columns		= array(
		'lockId',
		'userId',
		'subject',
		'entryId',
		'timestamp',
	);

	/**	@var	$name		string		Name of column with primary key */
	protected $primaryKey	= "lockId";

	/**	@var	$name		string		List of columns which are a foreign key and/or indexed */
	protected $indices		= array(
		'userId',
		'subject',
		'entryId',
	);

	/**	@var	$fetchMode	interger	Fetch mode, see PDO documentation */
	protected $fetchMode	= PDO::FETCH_OBJ;
}
