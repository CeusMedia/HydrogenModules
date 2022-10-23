<?php
/**
 *	Model for mission changes.
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Model for mission changes.
 *	@todo			code documentation
 */
class Model_Mission_Change extends Model
{
	/**	@var	$name		string		Table name without prefix of database connection */
	protected string $name			= "mission_changes";

	/**	@var	$name		string		List of columns within table */
	protected array $columns		= array(
		'missionChangeId',
		'missionId',
		'userId',
		'type',
		'data',
		'timestamp',
	);

	/**	@var	$name		string		Name of column with primary key */
	protected string $primaryKey	= "missionChangeId";

	/**	@var	$name		string		List of columns which are a foreign key and/or indexed */
	protected array $indices		= array(
		'missionId',
		'userId',
		'type',
	);

	/**	@var	$fetchMode	interger	Fetch mode, see PDO documentation */
	protected int $fetchMode	= PDO::FETCH_OBJ;
}
