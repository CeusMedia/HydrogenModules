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
	protected $name		= 'server_projects';

	protected $columns	= array(
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

	protected $primaryKey	= 'serverProjectId';

	protected $indices		= array(
		'serverId',
		'projectId',
		'status',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
