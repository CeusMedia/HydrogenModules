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
	protected $name			= 'bugs';

	protected $columns		= array(
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

	protected $primaryKey	= 'bugId';

	protected $indices		= array(
		'reporterId',
		'managerId',
		'type',
		'severity',
		'priority',
		'status',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
