<?php
/**
 *	Model to relate projects onto servers.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Model to relate projects onto servers.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 */
class Model_Server_Project extends Model
{
	protected string $name		= 'server_projects';

	protected array $columns	= array(
		'serverProjectId',
		'serverId',
		'projectId',
		'projectVersionId',
		'status',
		'version',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'serverProjectId';

	protected array $indices		= array(
		'serverId',
		'projectId',
		'status',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
