<?php
/**
 *	Model.
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Model.
 *	@todo			implement
 *	@todo			code documentation
 */
class Model_Novelty extends Model
{
	/**	@var	$name		string		Table name without prefix of database connection */
	protected string $name			= "novelties";

	/**	@var	$name		string		List of columns within table */
	protected array $columns		= array(
		'noveltyId',
		'userId',
		'entryId',
		'type',
		'timestamp',
	);

	/**	@var	$name		string		Name of column with primary key */
	protected string $primaryKey	= "noveltyId";

	/**	@var	$name		string		List of columns which are a foreign key and/or indexed */
	protected array $indices		= array(
		'userId',
		'entryId',
		'type',
		'timestamp',
	);

	/**	@var	$fetchMode	interger	Fetch mode, see PDO documentation */
	protected int $fetchMode	= PDO::FETCH_OBJ;
}