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
	protected $name		= 'projects';

	protected $columns	= array(
		'projectId',
		'status',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	);

	protected $primaryKey	= 'projectId';

	protected $indices		= array(
		'status',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
