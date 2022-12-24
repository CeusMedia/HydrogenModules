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
	/**	@var	string		$name			Table name without prefix of database connection */
	protected string $name					= "mission_changes";

	/**	@var	array		$columns		List of columns within table */
	protected array $columns				= [
		'missionChangeId',
		'missionId',
		'userId',
		'type',
		'data',
		'timestamp',
	];

	/**	@var	string		$primaryKey		Name of column with primary key */
	protected string $primaryKey			= "missionChangeId";

	/**	@var	array		$indices		List of columns which are a foreign key and/or indexed */
	protected array $indices				= [
		'missionId',
		'userId',
		'type',
	];

	/**	@var	integer		$fetchMode		Fetch mode, see PDO documentation */
	protected int $fetchMode				= PDO::FETCH_OBJ;
}
