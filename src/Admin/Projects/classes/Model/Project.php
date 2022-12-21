<?php
/**
 *	Project Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Project Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 */
class Model_Project extends Model
{
	protected string $name		= 'projects';

	protected array $columns	= array(
		'projectId',
		'status',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'projectId';

	protected array $indices		= array(
		'status',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
