<?php
/**
 *	Bug Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Bug Model.
 *	@category		cmApps
 *	@package		Chat.Server.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2024 Ceus Media (https://ceusmedia.de/)
 */
class Model_Bug extends Model
{
	protected string $name			= 'bugs';

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'bugId';

	protected array $indices		= [
		'reporterId',
		'managerId',
		'type',
		'severity',
		'priority',
		'status',
	];

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
