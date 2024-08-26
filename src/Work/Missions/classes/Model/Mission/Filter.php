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
	/**	@var	string		$name		Table name without prefix of database connection */
	protected string $name				= 'mission_filters';

	/**	@var	array		$columns	List of columns within table */
	protected array $columns			= [
		'missionFilterId',
		'userId',
		'serial',
		'timestamp',
	];

	/**	@var	string		$primaryKey	Name of column with primary key */
	protected string $primaryKey		= 'missionFilterId';

	/**	@var	array		$indices	List of columns which are a foreign key and/or indexed */
	protected array $indices			= [
		'userId',
	];

	/**	@var	integer		$fetchMode	Fetch mode, see PDO documentation */
	protected int $fetchMode			= PDO::FETCH_OBJ;
}
