<?php
/**
 *	Model for documents attached to missions.
 */
/**
*	Model for documents attached to missions.
 *	@todo			support linked files, e.G. by module Info:Files
 *	@todo			code documentation
 */
class Model_Mission_Document extends CMF_Hydrogen_Model
{
	/**	@var	$name		string		Table name without prefix of database connection */
	protected $name			= "mission_documents";

	/**	@var	$name		string		List of columns within table */
	protected $columns		= array(
		'missionDocumentId',
		'missionId',
		'userId',
		'mimeType',
		'size',
		'filename',
		'hashname',
		'createdAt',
		'modifiedAt',
		'accessedAt',
	);

	/**	@var	$name		string		Name of column with primary key */
	protected $primaryKey	= "missionDocumentId";

	/**	@var	$name		string		List of columns which are a foreign key and/or indexed */
	protected $indices		= array(
		'missionId',
		'userId',
		'mimeType',
		'filename',
	);

	/**	@var	$fetchMode	interger	Fetch mode, see PDO documentation */
	protected $fetchMode	= PDO::FETCH_OBJ;
}
