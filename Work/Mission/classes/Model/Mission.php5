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
class Model_Mission extends CMF_Hydrogen_Model{

	const TYPE_TASK		= 0;
	const TYPE_EVENT	= 1;
	
	/**	@var	$name		string		Table name without prefix of database connection */
	protected $name			= "missions";

	/**	@var	$name		string		List of columns within table */
	protected $columns		= array(
		'missionId',
		'ownerId',
		'workerId',
		'type',
		'priority',
		'status',
		'dayStart',
		'dayEnd',
		'timeStart',
		'timeEnd',
		'content',
		'location',
		'reference',
		'createdAt',
		'modifiedAt',
	);

	/**	@var	$name		string		Name of column with primary key */
	protected $primaryKey	= "missionId";

	/**	@var	$name		string		List of columns which are a foreign key and/or indexed */
	protected $indices		= array(
		'ownerId',
		'workerId',
		'type',
		'priority',
		'status',
		'dayStart',
		'dayEnd',
	);

	/**	@var	$fetchMode	interger	Fetch mode, see PDO documentation */
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>