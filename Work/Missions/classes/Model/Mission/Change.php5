<?php
/**
 *	Model.
 *	@version		$Id$
 */
/**
 *	Model.
 *	@version		$Id$
 *	@todo			implement
 *	@todo			code documentation
 */
class Model_Mission_Change extends CMF_Hydrogen_Model{

	/**	@var	$name		string		Table name without prefix of database connection */
	protected $name			= "mission_changes";

	/**	@var	$name		string		List of columns within table */
	protected $columns		= array(
		'missionChangeId',
		'missionId',
		'userId',
		'type',
		'data',
		'timestamp',
	);

	/**	@var	$name		string		Name of column with primary key */
	protected $primaryKey	= "missionChangeId";

	/**	@var	$name		string		List of columns which are a foreign key and/or indexed */
	protected $indices		= array(
		'missionId',
		'userId',
		'type',
	);

	/**	@var	$fetchMode	interger	Fetch mode, see PDO documentation */
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
