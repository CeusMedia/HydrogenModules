<?php
/**
 *	Bug Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Bug Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 */
class Model_Bug extends Model
{
	protected string $name			= 'bugs';

	protected array $columns		= array(
		'bugId',
		'reporterId',
		'managerId',
		'type',
		'severity',
		'priority',
		'status',
		'progress',
		'title',
		'content',
		'createdAt',
		'modifiedAt',
	);

	protected string $primaryKey	= 'bugId';

	protected array $indices		= array(
		'reporterId',
		'managerId',
		'type',
		'severity',
		'priority',
		'status',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
