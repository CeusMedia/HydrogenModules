<?php
/**
 *	Model for mission versions.
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Model for mission versions.
 *	@todo			code documentation
 */
class Model_Mission_Version extends Model
{
	/**	@var	$name		string		Table name without prefix of database connection */
	protected string $name			= "mission_versions";

	/**	@var	$name		string		List of columns within table */
	protected array $columns		= array(
		'missionVersionId',
		'missionId',
		'userId',
		'version',
		'content',
		'timestamp',
	);

	/**	@var	$name		string		Name of column with primary key */
	protected string $primaryKey	= "missionVersionId";

	/**	@var	$name		string		List of columns which are a foreign key and/or indexed */
	protected array $indices		= array(
		'missionId',
		'userId',
		'version',
	);

	/**	@var	$fetchMode	interger	Fetch mode, see PDO documentation */
	protected int $fetchMode	= PDO::FETCH_OBJ;
}
