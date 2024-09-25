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
	/**	@var	string		$name		Table name without prefix of database connection */
	protected string $name				= 'mission_documents';

	/**	@var	array		$columns	List of columns within table */
	protected array $columns			= [
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
	];

	/**	@var	string		$primaryKey	Name of column with primary key */
	protected string $primaryKey		= 'missionDocumentId';

	/**	@var	array		$indices	List of columns which are a foreign key and/or indexed */
	protected array $indices			= [
		'missionId',
		'userId',
		'mimeType',
		'filename',
	];

	/**	@var	integer		$fetchMode	Fetch mode, see PDO documentation */
	protected int $fetchMode				= PDO::FETCH_CLASS;

	/** @var	string		$className		Entity class to use */
	protected string $className				= 'Entity_Mission_Document';
}
