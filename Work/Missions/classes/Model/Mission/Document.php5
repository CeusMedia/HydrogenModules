<?php
/**
 *	Model for documents attached to missions.
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Model for documents attached to missions.
 *	@todo			support linked files, e.G. by module Info:Files
 *	@todo			code documentation
 */
class Model_Mission_Document extends Model
{
	/**	@var	$name		string		Table name without prefix of database connection */
	protected string $name			= "mission_documents";

	/**	@var	$name		string		List of columns within table */
	protected array $columns		= array(
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
	protected string $primaryKey	= "missionDocumentId";

	/**	@var	$name		string		List of columns which are a foreign key and/or indexed */
	protected array $indices		= array(
		'missionId',
		'userId',
		'mimeType',
		'filename',
	);

	/**	@var	$fetchMode	interger	Fetch mode, see PDO documentation */
	protected int $fetchMode	= PDO::FETCH_OBJ;
}
