<?php
/**
 *	Project Version Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Project Version Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 */
class Model_Project_Version extends Model
{
	protected string $name		= 'project_versions';

	protected array $columns	= array(
		'projectVersionId',
		'projectId',
		'status',
		'version',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'projectVersionId';

	protected array $indices		= array(
		'projectId',
		'status',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
