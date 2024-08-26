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
	/**	@var	string		$name		Table name without prefix of database connection */
	protected string $name				= 'mission_versions';

	/**	@var	array		$columns	List of columns within table */
	protected array $columns			= [
		'missionVersionId',
		'missionId',
		'userId',
		'version',
		'content',
		'timestamp',
	];

	/**	@var	string		$primaryKey	Name of column with primary key */
	protected string $primaryKey		= 'missionVersionId';

	/**	@var	array		$indices	List of columns which are a foreign key and/or indexed */
	protected array $indices			= [
		'missionId',
		'userId',
		'version',
	];

	/**	@var	integer		$fetchMode	Fetch mode, see PDO documentation */
	protected int $fetchMode			= PDO::FETCH_OBJ;
}
