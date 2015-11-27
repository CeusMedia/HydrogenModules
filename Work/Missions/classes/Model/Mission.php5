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

	const PRIORITY_NONE			= 0;
	const PRIORITY_HIGHEST		= 1;
	const PRIORITY_HIGH			= 2;
	const PRIORITY_NORMAL		= 3;
	const PRIORITY_LOW			= 4;
	const PRIORITY_LOWEST		= 5;

	const STATUS_ABORTED		= -2;
	const STATUS_REJECTED		= -1;
	const STATUS_NEW			= 0;
	const STATUS_ACCEPTED		= 1;
	const STATUS_PROGRESS		= 2;
	const STATUS_READY			= 3;
	const STATUS_FINISHED		= 4;

	/**	@var	$name		string		Table name without prefix of database connection */
	protected $name			= "missions";

	/**	@var	$name		string		List of columns within table */
	protected $columns		= array(
		'missionId',
		'creatorId',
		'modifierId',
		'workerId',
		'projectId',
		'type',
		'priority',
		'status',
		'dayStart',
		'dayEnd',
		'timeStart',
		'timeEnd',
		'minutesProjected',
		'minutesRequired',
		'title',
		'content',
		'location',
		'format',
		'reference',
		'createdAt',
		'modifiedAt',
	);

	/**	@var	$name		string		Name of column with primary key */
	protected $primaryKey	= "missionId";

	/**	@var	$name		string		List of columns which are a foreign key and/or indexed */
	protected $indices		= array(
		'creatorId',
		'modifierId',
		'workerId',
		'projectId',
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
