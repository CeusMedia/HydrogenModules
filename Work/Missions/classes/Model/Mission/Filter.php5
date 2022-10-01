<?php
/**
 *	Model for mission filters.
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Model for mission filters.
 *	@todo			code documentation
 */
class Model_Mission_Filter extends Model
{
	/**	@var	$name		string		Table name without prefix of database connection */
	protected $name			= "mission_filters";

	/**	@var	$name		string		List of columns within table */
	protected array $columns		= array(
		'missionFilterId',
		'userId',
		'serial',
		'timestamp',
	);

	/**	@var	$name		string		Name of column with primary key */
	protected string $primaryKey	= "missionFilterId";

	/**	@var	$name		string		List of columns which are a foreign key and/or indexed */
	protected array $indices		= array(
		'userId',
	);

	/**	@var	$fetchMode	interger	Fetch mode, see PDO documentation */
	protected int $fetchMode	= PDO::FETCH_OBJ;
}
