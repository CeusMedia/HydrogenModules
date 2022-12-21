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
class Model_Project_Server extends Model
{
	protected string $name		= 'project_servers';

	protected array $columns	= array(
		'projectServerId',
		'projectId',
		'serverId',
		'status',
		'version',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'projectServerId';

	protected array $indices		= array(
		'projectId',
		'serverId',
		'status',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
