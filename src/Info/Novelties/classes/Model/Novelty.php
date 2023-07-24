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
	/**	@var	string		$name		Table name without prefix of database connection */
	protected string $name				= "novelties";

	/**	@var	array		$name		List of columns within table */
	protected array $columns			= [
		'noveltyId',
		'userId',
		'entryId',
		'type',
		'timestamp',
	];

	/**	@var	string		$name		Name of column with primary key */
	protected string $primaryKey		= "noveltyId";

	/**	@var	array		$name		List of columns which are a foreign key and/or indexed */
	protected array $indices			= [
		'userId',
		'entryId',
		'type',
		'timestamp',
	];

	/**	@var	integer		$fetchMode	Fetch mode, see PDO documentation */
	protected int $fetchMode			= PDO::FETCH_OBJ;
}
